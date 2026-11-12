import argparse
from collections import Counter, defaultdict
from datetime import datetime, timezone
from itertools import combinations
from pathlib import Path

from sqlalchemy import delete, select
from sqlalchemy.orm import Session

from recommendation_database import create_sqlite_engine
from recommendation_models import (
    Base,
    BusinessRecommendation,
    Order,
    OrderProduct,
    Product,
    ProductRecommendation,
)

PROJECT_ROOT = Path(__file__).resolve().parents[1]
DEFAULT_DATABASE = PROJECT_ROOT / "database" / "database.sqlite"
MIN_SUPPORT = 0.05
MIN_CONFIDENCE = 0.10


def mine_pair_rules(
    transactions: list[set[int]],
    min_support: float = MIN_SUPPORT,
    min_confidence: float = MIN_CONFIDENCE,
) -> list[tuple[int, int, float, float, float]]:
    baskets = [set(transaction) for transaction in transactions if transaction]
    if not baskets:
        return []

    transaction_count = len(baskets)
    minimum_count = min_support * transaction_count
    singleton_counts = Counter(item for basket in baskets for item in basket)
    support_counts: dict[frozenset[int], int] = {
        frozenset((item,)): count
        for item, count in singleton_counts.items()
        if count >= minimum_count
    }
    previous_level = set(support_counts)
    itemset_size = 2

    while previous_level:
        candidates: set[frozenset[int]] = set()
        for left, right in combinations(sorted(previous_level, key=lambda group: tuple(sorted(group))), 2):
            candidate = left | right
            if len(candidate) == itemset_size and all(
                frozenset(subset) in previous_level
                for subset in combinations(candidate, itemset_size - 1)
            ):
                candidates.add(candidate)

        candidate_counts = Counter(
            candidate
            for basket in baskets
            for candidate in candidates
            if candidate.issubset(basket)
        )
        current_level = {
            candidate: count
            for candidate, count in candidate_counts.items()
            if count >= minimum_count
        }
        support_counts.update(current_level)
        previous_level = set(current_level)
        itemset_size += 1

    rules: list[tuple[int, int, float, float, float]] = []
    for pair, pair_count in support_counts.items():
        if len(pair) != 2:
            continue

        pair_support = pair_count / transaction_count
        for antecedent in pair:
            consequent = next(iter(pair - {antecedent}))
            antecedent_count = support_counts.get(frozenset((antecedent,)), 0)
            consequent_count = support_counts.get(frozenset((consequent,)), 0)
            if not antecedent_count or not consequent_count:
                continue

            confidence = pair_count / antecedent_count
            if confidence < min_confidence:
                continue

            consequent_support = consequent_count / transaction_count
            rules.append((
                antecedent,
                consequent,
                pair_support,
                confidence,
                confidence / consequent_support,
            ))

    return sorted(rules, key=lambda rule: (rule[0], -rule[3], -rule[4], rule[1]))


def load_product_baskets(session: Session) -> list[set[int]]:
    rows = session.execute(
        select(Order.id, Order.customer_id, Product.business_id, OrderProduct.product_id)
        .join(OrderProduct, OrderProduct.order_id == Order.id)
        .join(Product, Product.id == OrderProduct.product_id)
        .where(Order.status == "Entregado")
        .order_by(Order.id, OrderProduct.product_id)
    )
    baskets: dict[tuple[int, str, int], set[int]] = defaultdict(set)
    for order_id, customer_id, business_id, product_id in rows:
        history_key = ("customer", int(customer_id)) if customer_id is not None else ("order", int(order_id))
        key = (int(business_id), *history_key)
        baskets[key].add(int(product_id))
    return list(baskets.values())


def load_business_baskets(session: Session) -> list[set[int]]:
    rows = session.execute(
        select(Order.id, Order.customer_id, Order.business_id)
        .where(Order.status == "Entregado", Order.business_id.is_not(None))
        .order_by(Order.id, Order.business_id)
    )
    baskets: dict[tuple[str, int], set[int]] = defaultdict(set)
    for order_id, customer_id, business_id in rows:
        key = ("customer", int(customer_id)) if customer_id is not None else ("order", int(order_id))
        baskets[key].add(int(business_id))
    return list(baskets.values())


def train(
    database_path: Path,
    min_support: float = MIN_SUPPORT,
    min_confidence: float = MIN_CONFIDENCE,
) -> tuple[int, int]:
    engine = create_sqlite_engine(database_path)
    try:
        Base.metadata.create_all(
            engine,
            tables=[
                ProductRecommendation.__table__,
                BusinessRecommendation.__table__,
            ],
        )
        with Session(engine) as session, session.begin():
            product_rules = mine_pair_rules(
                load_product_baskets(session), min_support, min_confidence
            )
            business_rules = mine_pair_rules(
                load_business_baskets(session), min_support, min_confidence
            )
            timestamp = datetime.now(timezone.utc).isoformat()

            session.execute(delete(ProductRecommendation))
            session.execute(delete(BusinessRecommendation))
            session.add_all(
                [
                    ProductRecommendation(
                        product_id=antecedent,
                        recommended_product_id=consequent,
                        support=support,
                        confidence=confidence,
                        lift=lift,
                        created_at=timestamp,
                        updated_at=timestamp,
                    )
                    for antecedent, consequent, support, confidence, lift in product_rules
                ]
            )
            session.add_all(
                [
                    BusinessRecommendation(
                        business_id=antecedent,
                        recommended_business_id=consequent,
                        support=support,
                        confidence=confidence,
                        lift=lift,
                        created_at=timestamp,
                        updated_at=timestamp,
                    )
                    for antecedent, consequent, support, confidence, lift in business_rules
                ]
            )
    finally:
        engine.dispose()

    return len(product_rules), len(business_rules)


def main() -> None:
    parser = argparse.ArgumentParser(description="Entrena recomendaciones con Apriori.")
    parser.add_argument("--database", type=Path, default=DEFAULT_DATABASE)
    parser.add_argument("--min-support", type=float, default=MIN_SUPPORT)
    parser.add_argument("--min-confidence", type=float, default=MIN_CONFIDENCE)
    args = parser.parse_args()

    if not 0 < args.min_support <= 1:
        parser.error("--min-support debe estar en el rango (0, 1].")
    if not 0 <= args.min_confidence <= 1:
        parser.error("--min-confidence debe estar en el rango [0, 1].")

    product_count, business_count = train(args.database, args.min_support, args.min_confidence)
    print(f"Reglas de producto guardadas: {product_count}")
    print(f"Reglas de restaurante guardadas: {business_count}")


if __name__ == "__main__":
    main()

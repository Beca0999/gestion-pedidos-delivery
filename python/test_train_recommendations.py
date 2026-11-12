import sqlite3
import tempfile
import unittest
from pathlib import Path

from train_recommendations import mine_pair_rules, train


class AprioriTrainingTest(unittest.TestCase):
    def test_pair_rules_calculate_support_confidence_and_lift(self):
        rules = mine_pair_rules(
            [{1, 2}, {1, 2}, {1}, {2}],
            min_support=0.5,
            min_confidence=0.6,
        )

        rule = next(rule for rule in rules if rule[:2] == (1, 2))
        self.assertAlmostEqual(rule[2], 0.5)
        self.assertAlmostEqual(rule[3], 2 / 3)
        self.assertAlmostEqual(rule[4], 8 / 9)

    def test_training_persists_product_rules_separately_for_each_business(self):
        with tempfile.TemporaryDirectory() as temporary_directory:
            database_path = Path(temporary_directory) / "delivery.sqlite"
            with sqlite3.connect(database_path) as connection:
                connection.executescript(
                    """
                    CREATE TABLE businesses (id INTEGER PRIMARY KEY, name TEXT);
                    CREATE TABLE products (
                        id INTEGER PRIMARY KEY,
                        business_id INTEGER NOT NULL REFERENCES businesses(id)
                    );
                    CREATE TABLE orders (
                        id INTEGER PRIMARY KEY,
                        customer_id INTEGER,
                        business_id INTEGER,
                        status TEXT
                    );
                    CREATE TABLE order_product (
                        order_id INTEGER,
                        product_id INTEGER
                    );
                    INSERT INTO businesses VALUES (10, 'Restaurante A'), (20, 'Restaurante B');
                    INSERT INTO products VALUES
                        (100, 10), (101, 10), (200, 20), (201, 20);
                    INSERT INTO orders VALUES
                        (1, 1, 10, 'Entregado'),
                        (2, 1, 20, 'Entregado'),
                        (3, 2, 10, 'Entregado'),
                        (4, 2, 20, 'Entregado'),
                        (5, NULL, 10, 'Entregado'),
                        (6, NULL, 20, 'Entregado');
                    INSERT INTO order_product VALUES
                        (1, 100), (1, 101),
                        (2, 200), (2, 201),
                        (3, 100), (3, 101),
                        (4, 200), (4, 201),
                        (5, 100), (5, 101),
                        (6, 200), (6, 201);
                    """
                )

            product_count, business_count = train(
                database_path,
                min_support=0.5,
                min_confidence=0.5,
            )

            self.assertEqual(product_count, 4)
            self.assertEqual(business_count, 2)
            with sqlite3.connect(database_path) as connection:
                product_rules = connection.execute(
                    """
                    SELECT product_id, recommended_product_id
                    FROM product_recommendations
                    ORDER BY product_id
                    """
                ).fetchall()
                business_rules = connection.execute(
                    """
                    SELECT business_id, recommended_business_id
                    FROM business_recommendations
                    ORDER BY business_id
                    """
                ).fetchall()

            self.assertEqual(product_rules, [(100, 101), (101, 100), (200, 201), (201, 200)])
            self.assertEqual(business_rules, [(10, 20), (20, 10)])


if __name__ == "__main__":
    unittest.main()

import os
from pathlib import Path

from flask import Flask, jsonify, request
from sqlalchemy import select
from sqlalchemy.exc import SQLAlchemyError
from sqlalchemy.orm import Session

from recommendation_database import create_sqlite_engine
from recommendation_models import (
    Business,
    BusinessRecommendation,
    Product,
    ProductRecommendation,
)


PROJECT_ROOT = Path(__file__).resolve().parents[1]
DEFAULT_DATABASE = PROJECT_ROOT / "database" / "database.sqlite"

app = Flask(__name__)


@app.get("/api/recommendations")
def get_recommendations():
    raw_product_id = request.args.get("product_id")
    try:
        product_id = int(raw_product_id)
        if product_id < 1:
            raise ValueError
    except (TypeError, ValueError):
        return jsonify({"error": "product_id debe ser un entero positivo."}), 400

    database_path = Path(os.environ.get("LARAVEL_DB_PATH", DEFAULT_DATABASE))
    if not database_path.is_file():
        return jsonify({"error": "No se encontró la base SQLite configurada."}), 503

    engine = create_sqlite_engine(database_path)
    try:
        with Session(engine) as session:
            product = session.get(Product, product_id)
            if product is None:
                return jsonify({"error": "Producto no encontrado."}), 404

            rows = session.execute(
                select(
                    Product.id,
                    Product.name,
                    Product.price,
                    Product.business_id,
                    Business.name.label("business_name"),
                    ProductRecommendation.confidence,
                    ProductRecommendation.lift,
                )
                .join(
                    Product,
                    Product.id == ProductRecommendation.recommended_product_id,
                )
                .join(Business, Business.id == Product.business_id)
                .where(ProductRecommendation.product_id == product_id)
                .order_by(ProductRecommendation.confidence.desc())
                .limit(3)
            ).mappings().all()
    except SQLAlchemyError:
        app.logger.exception("No se pudieron leer las recomendaciones de SQLite.")
        return jsonify({"error": "No se pudieron consultar las recomendaciones."}), 503
    finally:
        engine.dispose()

    return jsonify(
        {
            "product_id": product_id,
            "recommendations": [dict(row) for row in rows],
        }
    )


@app.get("/api/businesses/<int:business_id>/recommendations")
def get_business_recommendations(business_id: int):
    database_path = Path(os.environ.get("LARAVEL_DB_PATH", DEFAULT_DATABASE))
    if not database_path.is_file():
        return jsonify({"error": "No se encontró la base SQLite configurada."}), 503

    engine = create_sqlite_engine(database_path)
    try:
        with Session(engine) as session:
            business = session.get(Business, business_id)
            if business is None:
                return jsonify({"error": "Restaurante no encontrado."}), 404

            rows = session.execute(
                select(
                    Business.id,
                    Business.name,
                    Business.delivery_price,
                    BusinessRecommendation.confidence,
                    BusinessRecommendation.lift,
                )
                .join(
                    Business,
                    Business.id == BusinessRecommendation.recommended_business_id,
                )
                .where(BusinessRecommendation.business_id == business_id)
                .order_by(
                    BusinessRecommendation.confidence.desc(),
                    BusinessRecommendation.lift.desc(),
                )
                .limit(3)
            ).mappings().all()
    except SQLAlchemyError:
        app.logger.exception("No se pudieron leer las recomendaciones de restaurantes.")
        return jsonify({"error": "No se pudieron consultar las recomendaciones."}), 503
    finally:
        engine.dispose()

    return jsonify(
        {
            "business_id": business_id,
            "recommendations": [dict(row) for row in rows],
        }
    )


if __name__ == "__main__":
    app.run(
        host=os.environ.get("FLASK_HOST", "127.0.0.1"),
        port=int(os.environ.get("FLASK_PORT", "5000")),
        debug=False,
    )

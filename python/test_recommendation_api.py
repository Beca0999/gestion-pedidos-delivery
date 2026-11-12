import os
import sqlite3
import tempfile
import unittest
from pathlib import Path
from unittest.mock import patch

from recommendation_api import app


class RecommendationApiTest(unittest.TestCase):
    def setUp(self):
        self.temporary_directory = tempfile.TemporaryDirectory()
        self.database_path = Path(self.temporary_directory.name) / "delivery.sqlite"
        with sqlite3.connect(self.database_path) as connection:
            connection.executescript(
                """
                CREATE TABLE businesses (id INTEGER PRIMARY KEY, name TEXT, delivery_price REAL);
                CREATE TABLE products (
                    id INTEGER PRIMARY KEY,
                    business_id INTEGER,
                    name TEXT,
                    price REAL
                );
                CREATE TABLE product_recommendations (
                    product_id INTEGER,
                    recommended_product_id INTEGER,
                    confidence REAL,
                    lift REAL
                );
                CREATE TABLE business_recommendations (
                    business_id INTEGER,
                    recommended_business_id INTEGER,
                    confidence REAL,
                    lift REAL
                );
                INSERT INTO businesses VALUES
                    (1, 'Taquería Centro', 25),
                    (2, 'Pizzería Norte', 30);
                INSERT INTO products VALUES (10, 1, 'Tacos', 90), (20, 2, 'Pizza', 150);
                INSERT INTO product_recommendations VALUES (10, 20, 0.75, 1.4);
                INSERT INTO business_recommendations VALUES (1, 2, 0.6, 1.3);
                """
            )

        self.environment = patch.dict(os.environ, {"LARAVEL_DB_PATH": str(self.database_path)})
        self.environment.start()
        app.config.update(TESTING=True)
        self.client = app.test_client()

    def tearDown(self):
        self.environment.stop()
        self.temporary_directory.cleanup()

    def test_product_recommendations_include_the_recommended_restaurant(self):
        response = self.client.get("/api/recommendations?product_id=10")

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json["recommendations"][0]["business_name"], "Pizzería Norte")

    def test_product_id_rejects_non_integer_input(self):
        response = self.client.get("/api/recommendations?product_id=10%20OR%201=1")

        self.assertEqual(response.status_code, 400)

    def test_business_recommendations_return_related_restaurants(self):
        response = self.client.get("/api/businesses/1/recommendations")

        self.assertEqual(response.status_code, 200)
        self.assertEqual(response.json["recommendations"][0]["name"], "Pizzería Norte")


if __name__ == "__main__":
    unittest.main()

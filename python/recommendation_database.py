from pathlib import Path

from sqlalchemy import URL, create_engine, event
from sqlalchemy.engine import Engine


def create_sqlite_engine(database_path: Path) -> Engine:
    engine = create_engine(
        URL.create("sqlite+pysqlite", database=str(database_path)),
        connect_args={"timeout": 30},
    )

    @event.listens_for(engine, "connect")
    def enable_sqlite_foreign_keys(connection, _record) -> None:
        cursor = connection.cursor()
        cursor.execute("PRAGMA foreign_keys=ON")
        cursor.close()

    return engine

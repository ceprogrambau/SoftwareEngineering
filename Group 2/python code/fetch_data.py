import mysql.connector
import pandas as pd
from shared_variables import host, user, password, database
import time

def fetch_all_tables_from_mysql():
    print("Fetching all tables from MySQL")
    tableslist = []
    tables_dict = {}
    mydb = None       
    cursor = None     

    try:
        mydb = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        cursor = mydb.cursor()
        cursor.execute("SHOW TABLES;")
        tables = cursor.fetchall()
        for (table_name,) in tables:
            table_name = table_name.decode() if isinstance(table_name, (bytes, bytearray)) else table_name
            cursor.execute(f"SHOW COLUMNS FROM `{table_name}`;")
            columns = [col[0] for col in cursor.fetchall()]  
            
            tables_dict[table_name] = columns

    except mysql.connector.Error as err:
        print(f"Error: {err}")
        tables_dict = None 

    finally:
        if cursor:
            cursor.close()
        if mydb and mydb.is_connected():
            mydb.close()

    print("Tables fetched successfully")

    return tables_dict

def fetch_table(table):
    start_time = time.time()
    print(f"Fetching table: {table}")
    try:
        mydb = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        cursor = mydb.cursor()
        cursor.execute(f"SELECT * FROM {table};")
        rows = cursor.fetchall()
        columns = [col[0] for col in cursor.description] #fetching column names
        df = pd.DataFrame(rows, columns=columns)
        print(f"Table {table} fetched successfully")
        end_time = time.time()
        print(f"Time taken: {end_time - start_time} seconds to fetch table {table}")
        return df
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        print(f"Table {table} not found")
        return None
    finally:
        if cursor:
            cursor.close()
        if mydb and mydb.is_connected():
            mydb.close()
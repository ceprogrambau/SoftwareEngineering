import mysql.connector
import pandas as pd
from shared_variables import host, user, password, database

def fetch_all_tables_from_mysql():
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

    return tables_dict

def fetch_table(table):
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
        columns = [col[0] for col in cursor.description]
        df = pd.DataFrame(rows, columns=columns)
        return df
    except mysql.connector.Error as err:
        print(f"Error: {err}")
        return None
    finally:
        if cursor:
            cursor.close()
        if mydb and mydb.is_connected():
            mydb.close()

import mysql.connector
import pandas as pd

# Your original function to fetch table schema
def fetch_all_tables_from_mysql():
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
            # Decode if table_name is bytes
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

    return tables_dict


def fetch_all_tables_data_to_df():
    tables_schema = fetch_all_tables_from_mysql()
    if tables_schema is None:
        print("Failed to retrieve table schema.")
        return None

    dataframes = {}
    mydb = None
    cursor = None

    try:
        # Establish a new connection to fetch data
        mydb = mysql.connector.connect(
            host=host,
            user=user,
            password=password,
            database=database
        )
        cursor = mydb.cursor()

        # Loop over each table and fetch its data
        for table, columns in tables_schema.items():
            query = f"SELECT * FROM `{table}`;"
            cursor.execute(query)
            rows = cursor.fetchall()
            # Create a DataFrame using the fetched rows and the known column names
            df = pd.DataFrame(rows, columns=columns)
            dataframes[table] = df

    except mysql.connector.Error as err:
        print(f"Error while fetching table data: {err}")
        dataframes = None

    finally:
        if cursor:
            cursor.close()
        if mydb and mydb.is_connected():
            mydb.close()

    return dataframes


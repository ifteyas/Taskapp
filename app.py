from flask import Flask, render_template, request
from flask_mysqldb import MySQL

app = Flask(__name__)

# Database Configuration
app.config['MYSQL_USER'] = 'root'
app.config['MYSQL_PASSWORD'] = ''
app.config['MYSQL_DB'] = 'testdb'
app.config['MYSQL_HOST'] = 'localhost'

mysql = MySQL(app)

# Create categories table if it doesn't exist
create_categories_table_query = """
    CREATE TABLE IF NOT EXISTS categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    )
"""

# Create tasks table if it doesn't exist
create_tasks_table_query = """
    CREATE TABLE IF NOT EXISTS tasks (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(100) NOT NULL,
        description TEXT,
        category_id INT,
        FOREIGN KEY (category_id) REFERENCES categories(id)
    )
"""

with app.app_context():
    conn = mysql.connection
    cursor = conn.cursor()
    cursor.execute(create_categories_table_query)
    cursor.execute(create_tasks_table_query)
    conn.commit()
    cursor.close()

@app.route('/')
def index():
    # Fetch tasks with category names
    tasks_query = """
        SELECT tasks.id, tasks.title, tasks.description, categories.name as category_name 
        FROM tasks 
        INNER JOIN categories ON tasks.category_id = categories.id
    """
    cursor = mysql.connection.cursor()
    cursor.execute(tasks_query)
    tasks_result = cursor.fetchall()

    # Fetch all categories
    cursor.execute("SELECT * FROM categories")
    categories_result = cursor.fetchall()

    cursor.close()

    return render_template('index.html', tasks=tasks_result, categories=categories_result)

@app.route('/add_task', methods=['POST'])
def add_task():
    title = request.form['title']
    description = request.form['description']
    category_id = request.form['category']

    insert_task_query = "INSERT INTO tasks (title, description, category_id) VALUES (%s, %s, %s)"
    data = (title, description, category_id)

    conn = mysql.connection
    cursor = conn.cursor()
    cursor.execute(insert_task_query, data)
    conn.commit()
    cursor.close()

    return index()

@app.route('/add_category', methods=['POST'])
def add_category():
    name = request.form['name']

    insert_category_query = "INSERT INTO categories (name) VALUES (%s)"
    data = (name,)

    conn = mysql.connection
    cursor = conn.cursor()
    cursor.execute(insert_category_query, data)
    conn.commit()
    cursor.close()

    return index()

@app.route('/search', methods=['GET', 'POST'])
def search():
    if request.method == 'POST':
        search_query = request.form['search_query']
        search_query_like = f"%{search_query}%"

        search_tasks_query = """
            SELECT tasks.id, tasks.title, tasks.description, categories.name as category_name 
            FROM tasks 
            INNER JOIN categories ON tasks.category_id = categories.id
            WHERE tasks.title LIKE %s OR categories.name LIKE %s
        """

        cursor = mysql.connection.cursor()
        cursor.execute(search_tasks_query, (search_query_like, search_query_like))
        search_result = cursor.fetchall()
        cursor.close()

        return render_template('search.html', search_result=search_result, search_query=search_query)

    return render_template('search.html')

if __name__ == '__main__':
    app.run(debug=True)

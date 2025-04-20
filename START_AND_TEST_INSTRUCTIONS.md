# How to Start the Project and Test Functionalities

## 1. Start the Backend

The backend is PHP-based and provides REST API endpoints.

To start the backend server, run the built-in PHP server pointing to the `backend` directory. From the project root directory, run this command in your terminal:

```
php -S localhost:8000 -t backend
```

This will start the backend server at [http://localhost:8000](http://localhost:8000), serving the API endpoints.

Make sure you have PHP installed on your system and the MySQL database is running with the correct credentials as configured in `backend/config/database.php`.

## 2. Start the Frontend

The frontend is a React application.

Navigate to the `frontend` directory and install dependencies:

```
cd frontend
npm install
```

Then start the React development server:

```
npm start
```

This will start the frontend app at [http://localhost:3000](http://localhost:3000).

## 3. Test Different Functionalities

- Open your browser and go to [http://localhost:3000](http://localhost:3000).
- Use the UI to test different functionalities such as managing clients and invoices.
- The frontend communicates with the backend API to perform CRUD operations.
- You can verify backend API responses by checking the network tab in your browser developer tools or by using API testing tools like Postman.

## Additional Notes

- Ensure your MySQL database is set up and running. You can use the `backend/schema.sql` file to create the necessary database and tables.
- If you need to reset the database, run the SQL script in your MySQL client.
- The backend API endpoints are located in the `backend/api/` directory.
- The frontend services that interact with the backend API are in `frontend/src/services/`.

This should help you start and test the project effectively.

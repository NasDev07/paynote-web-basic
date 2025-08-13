## PayNote Web App

This web application is designed to help users easily monitor their expenses and income. With a simple and intuitive interface, users can add, modify, or delete transactions at any time. In addition, the application provides a clear financial summary and interactive charts to visualize the flow of expenses and income.

## Preview 
|![image](https://ucarecdn.com/50520629-fd42-4290-8cbb-fd4092e477db/paynote.png)|![image](https://ucarecdn.com/812dc1e9-ca0e-4517-893c-5a77f6e5724f/Screenshot_20240219_123233.png)|![image](https://ucarecdn.com/ad3f3411-175e-4111-bb08-29a6ff1a3c1d/Screenshot_20240219_123356.png)|
|---|---|---|

## Technologies Used
- Laravel 10
- PHP 8
- MySQL
- Bootstrap 5
- SASS

## Features
- Add, edit, and delete transactions
- Summary of transactions
- Responsive design
- User authentication
- User registration
- Etc.

## Installation
1. Clone the repository
```bash
git clone git@github.com:andikatuluspangestu/paynote-web.git
```

2. Install the dependencies
```bash
composer install
```

3. Create a new database and import the database schema from the `database` directory
4. Create a new `.env` file and update the database configuration
5. Generate a new application key
```bash
php artisan key:generate
```

6. Run the application
```bash
php artisan serve
```

7. Open the application in your browser
```bash
http://localhost:8000
```

## License
This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# Job Seeker Project

## Overview

The **Job Seeker Project** is a web application built using the Symfony framework, designed to help job seekers find job opportunities, create profiles, apply for jobs, and manage their applications. The platform allows users to create an account, search for jobs based on various criteria, and upload resumes to apply for positions.

## Features

- **User Authentication**: Secure login and registration system for job seekers.
- **Job Search**: Ability to search for jobs based on location, job type, and keywords.
- **Profile Management**: Users can create and manage their profiles, including uploading resumes and personal details.
- **Job Applications**: Apply for jobs directly through the platform with a simple application process.
- **Admin Panel**: Admins can add, edit, or remove job listings and manage users.

## Prerequisites

To run this project locally, you'll need the following:

- PHP >= 5.5.9
- Composer (PHP dependency manager)
- Symfony CLI (optional but recommended for development)
- MongoDB database (for development)

## Installation

### 1. Clone the repository

```bash
git clone https://github.com/daddyauden/jobseeker.git
cd job-seeker
````

### 2. Install dependencies

Run the following command to install the required PHP dependencies using Composer:

```bash
composer install
```

### 3. Set up the environment

Update config file in the directory and configure your database connection:

```bash
app/config/config_{env}.yml
```

### 4. Create the database

If you haven't created the database yet, you can create it by running:

```bash
php bin/console doctrine:database:create
```

### 5. Run migrations

Run the database migrations to create the necessary tables:

```bash
php bin/console doctrine:migrations:migrate
```

### 6. (Optional) Load fixtures

If you want to load sample data into the database (e.g., sample jobs or user accounts), you can use Doctrine fixtures:

```bash
php bin/console doctrine:fixtures:load
```

### 7. Start the development server

To start a local Symfony development server:

```bash
php bin/console server:run
```

Alternatively, if you have Symfony CLI installed, you can run:

```bash
symfony serve
```

The application should now be accessible at `http://127.0.0.1:8000`.

## Usage

Once the application is running, visit the homepage at `http://127.0.0.1:8000` to start searching for jobs, creating an account, or managing your profile.

### User Flow

1. **Register**: Create an account by signing up with your email and password.
2. **Search Jobs**: Use the search feature to find jobs that match your skills and preferences.
3. **Apply**: Apply for jobs by submitting your profile and uploading a resume.
4. **Profile Management**: Update your profile with your latest information and documents.

### Admin Features

Admins have additional access to:

* **Manage Job Listings**: Add, edit, or remove job listings from the platform.
* **User Management**: View and manage job seeker accounts.

## Testing

Run tests using Symfony's testing tools:

```bash
php bin/console doctrine:fixtures:load --env=test
php bin/console test:run
```

## Technologies Used

* **Symfony Framework**: The core PHP framework used to build the application.
* **Twig**: The templating engine for rendering views.
* **Doctrine ORM**: The Object-Relational Mapper for database interaction.
* **MongoDB**: The database used for storing user and job information.
* **Bootstrap**: Frontend framework for responsive design.

## Contributing

We welcome contributions to the project! To contribute:

1. Fork the repository.
2. Create a new branch (`git checkout -b feature/your-feature`).
3. Commit your changes (`git commit -am 'Add new feature'`).
4. Push to the branch (`git push origin feature/your-feature`).
5. Open a pull request.

## License

This project is licensed under the GNU License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

* [Symfony](https://symfony.com) for providing the framework.
* [Bootstrap](https://getbootstrap.com) for the frontend framework.
* [Doctrine](https://www.doctrine-project.org/) for the ORM.

```

This `README.md` gives a clear guide on setting up and using the Job Seeker application, outlining the necessary steps, features, and technologies involved. Be sure to replace `https://github.com/your-username/job-seeker.git` with the actual URL of your repository.
```

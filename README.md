# Expense Tracker

## Overview

Expense Tracker is an API for managing expenses. It allows users to create, update, retrieve, and delete expense records, providing functionality to monitor and manage personal finances programmatically.

## Table of Contents


## Installation

1.  **Build and Start the Docker Containers**
    Use the `make setup` command to build Docker images, start the containers, and apply any necessary database migrations:
2. After running Api endpoints available on http://localhost:8000 or http://localhost:8080


### Prerequisites

- [Docker Desktop](https://www.docker.com/products/docker-desktop) (for running Symfony with Docker, includes Docker Engine and Docker Compose)

### If Not Using Docker

- [PHP](https://www.php.net/) (v8.2 or later) - Required for running Symfony.
- [Composer](https://getcomposer.org/) - For managing PHP dependencies.
- [MySQL](https://www.mysql.com/) - (v8.0) For database management.

- composer install
- php bin/console doctrine:migrations:migrate --no-interaction
- php -S 127.0.0.1:8000 -t public   or setup virtual host and run


### Setting Up 

1. **Clone the Repository**

   ```bash
   git clone https://github.com/purathoot/Expense-Tracker.git
   cd Expense-Tracker

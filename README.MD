Installation:

Clone the repository:
`git clone https://github.com/MesutMG/biletcominternship_2.git`
`cd biletcominternship_2`

Install dependencies:
`composer install`
`npm install`

Copy the example environment file and update your database credentials:
`cp .env.example .env`

Generate application key:
`php artisan key:generate`

Run database migrations
`php artisan migrate`

Start the Vite development server:
`npm run dev`

Start the Laravel local server (in a separate terminal):
`php artisan serve`

Navigate to http://localhost:8000
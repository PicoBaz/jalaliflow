# JalaliFlow Documentation

**JalaliFlow** is a powerful Laravel package designed to handle Persian (Jalali) calendar operations with ease. It provides advanced date conversion, holiday management, and event scheduling, seamlessly integrated with Laravel’s ecosystem. With support for multiple calendars (Jalali, Gregorian, etc.), multilingual formatting, and developer-friendly tools, JalaliFlow is ideal for projects targeting Persian-speaking audiences or requiring cross-calendar functionality.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
  - [Converting Dates](#converting-dates)
  - [Checking Holidays](#checking-holidays)
  - [Managing Events](#managing-events)
  - [Eloquent Trait](#eloquent-trait)
  - [Artisan Command](#artisan-command)
- [Advanced Usage](#advanced-usage)
- [Contributing](#contributing)
- [License](#license)

## Features
- **Date Conversion**: Convert between Jalali, Gregorian, and other calendars with customizable formats.
- **Holiday Management**: Check official Persian holidays with a preloaded list (extendable via APIs).
- **Event Scheduling**: Add and manage events with support for repetition (daily, weekly, etc.).
- **Laravel Integration**:
  - Eloquent Trait for automatic date conversion in models.
  - Artisan command for listing holidays.
  - Blade directives and middleware for locale-aware date handling.
- **Multilingual Support**: Display dates in Persian, English, Arabic, or other languages.
- **Configurable**: Customize date formats, timezones, and locales via a configuration file.
- **Extensible**: Integrate with external APIs (e.g., Google Calendar) for advanced functionality.

## Requirements
- PHP 8.0 or higher
- Laravel 9.0 or 10.0
- Composer

## Installation
1. Install the package via Composer:
   ```bash
   composer require picobaz/jalaliflow
   ```

2. Publish the configuration file (optional):
   ```bash
   php artisan vendor:publish --tag=config
   ```
   This will create a `config/jalaliflow.php` file for customizing settings.

3. (Optional) Add the facade to your `config/app.php` (if not auto-registered):
   ```php
   'aliases' => [
       'JalaliFlow' => PicoBaz\JalaliFlow\Facades\JalaliFlow::class,
   ],
   ```

## Configuration
The `config/jalaliflow.php` file allows you to customize the package’s behavior. Default settings include:

```php
return [
    'date_format' => 'Y/m/d', // Default Jalali date format
    'locale' => 'fa',        // Default language for date display
    'timezone' => 'Asia/Tehran', // Default timezone
];
```

You can modify these settings to suit your project’s needs, such as changing the date format to `Y-m-d` or setting the locale to `en` for English.

## Usage

### Converting Dates
Convert Gregorian dates to Jalali and vice versa using the `JalaliFlow` facade.

```php
use PicoBaz\JalaliFlow\Facades\JalaliFlow;

// Convert Gregorian to Jalali
$jalaliDate = JalaliFlow::toJalali('2025-05-14'); // Output: 1404/02/24

// Convert Jalali to Gregorian
$gregorianDate = JalaliFlow::toGregorian('1404/02/24'); // Output: 2025-05-14

// Custom format
$formattedDate = JalaliFlow::toJalali('2025-05-14', 'l، d F Y'); // Output: سه‌شنبه، 24 اردیبهشت 1404
```

### Checking Holidays
Check if a specific Jalali date is a holiday (based on a preloaded list).

```php
$isHoliday = JalaliFlow::isHoliday('1404/01/01'); // Output: true (Norouz)
$isHoliday = JalaliFlow::isHoliday('1404/02/24'); // Output: false
```

> **Note**: The holiday list is static in this version. To fetch real-time holidays, integrate with an external API (see [Advanced Usage](#advanced-usage)).

### Managing Events
Add and manage events with optional repetition.

```php
$event = JalaliFlow::addEvent([
    'title' => 'Team Meeting',
    'date' => '1404/02/25',
    'repeat' => 'weekly',
]);

// Output: ['title' => 'Team Meeting', 'date' => '1404/02/25', 'repeat' => 'weekly']
```

> **Note**: This is a basic implementation. For persistent storage, consider integrating with a database or Google Calendar.

### Eloquent Trait
Use the `JalaliDate` trait to automatically convert dates in your Eloquent models.

```php
use Illuminate\Database\Eloquent\Model;
use PicoBaz\JalaliFlow\Traits\JalaliDate;

class Post extends Model
{
    use JalaliDate;
}
```

Access the `jalali_date` attribute to get the `created_at` date in Jalali format:

```php
$post = Post::first();
echo $post->jalali_date; // Output: 1404/02/24
```

### Artisan Command
List holidays for a specific Jalali year using the provided Artisan command.

```bash
php artisan jalali:holidays 1404
```

Output:
```
Holidays for 1404:
1404/01/01: Norouz
1404/01/02: Norouz
```

## Advanced Usage
### Custom Date Formats
Override the default format in your code or configuration:

```php
$customDate = JalaliFlow::toJalali('2025-05-14', 'Y-m-d H:i:s'); // Output: 1404-02-24 00:00:00
```

### Integrating with Google Calendar
To sync events with Google Calendar, you’ll need to set up OAuth credentials and use a library like `google/apiclient`. Example (requires additional setup):

```php
use Google\Client;
use Google\Service\Calendar;

$client = new Client();
// Configure OAuth credentials
$service = new Calendar($client);

$event = new \Google\Service\Calendar\Event([
    'summary' => 'Team Meeting',
    'start' => ['date' => JalaliFlow::toGregorian('1404/02/25')],
    'end' => ['date' => JalaliFlow::toGregorian('1404/02/25')],
]);

$calendarId = 'primary';
$service->events->insert($calendarId, $event);
```

### Extending Holiday Data
To fetch holidays dynamically, integrate with an API (e.g., a government holiday API). Example using Guzzle:

```php
use GuzzleHttp\Client;

$client = new Client();
$response = $client->get('https://api.example.com/holidays/1404');
$holidays = json_decode($response->getBody(), true);

// Update JalaliFlow holidays
JalaliFlow::setHolidays($holidays);
```

## Contributing
We welcome contributions! To contribute:
1. Fork the repository at [https://github.com/PicoBaz/JalaliFlow](https://github.com/PicoBaz/JalaliFlow).
2. Create a feature branch (`git checkout -b feature/YourFeature`).
3. Commit your changes (`git commit -m "Add YourFeature"`).
4. Push to the branch (`git push origin feature/YourFeature`).
5. Open a pull request.

Please include tests and update the documentation for new features.

## License
JalaliFlow is open-source software licensed under the [MIT License](LICENSE).

---

**Support**: For questions or issues, create a GitHub issue at [https://github.com/PicoBaz/JalaliFlow/issues](https://github.com/PicoBaz/JalaliFlow/issues) or contact us at [picobaz3@gmail.com](picobaz3@gmail.com).

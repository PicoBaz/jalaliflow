# JalaliFlow Documentation

**JalaliFlow** is a powerful Laravel package designed to handle Persian (Jalali) calendar operations with ease. It provides advanced date conversion, holiday management, and event scheduling, seamlessly integrated with Laravel’s ecosystem. With support for Jalali and Gregorian calendars, holiday checking, and automated event scheduling, JalaliFlow is ideal for projects targeting Persian-speaking audiences.

## Table of Contents
- [Features](#features)
- [Requirements](#requirements)
- [Installation](#installation)
- [Configuration](#configuration)
- [Usage](#usage)
    - [Converting Dates](#converting-dates)
    - [Adding Dates](#adding-dates)
    - [Subtracting Dates](#subtracting-dates)
    - [Calculating Differences](#calculating-differences)
    - [Validating Jalali Dates](#validating-jalali-dates)
    - [Checking Holidays](#checking-holidays)
    - [Managing Events](#managing-events)
    - [Eloquent Trait](#eloquent-trait)
    - [Artisan Command](#artisan-command)
- [Advanced Usage](#advanced-usage)
- [Contributing](#contributing)
- [License](#license)

## Features
- **Date Conversion**: Convert between Jalali and Gregorian calendars with customizable formats.
- **Date Manipulation**: Add or subtract days, weeks, months, or years, respecting Jalali calendar rules.
- **Holiday Management**: Check official Persian holidays with a preloaded list.
- **Event Scheduling**: Schedule events to run daily, weekly, monthly, or yearly with database persistence.
- **Laravel Integration**:
    - Eloquent Trait for automatic date conversion in models.
    - Artisan commands for listing holidays and running scheduled events.
- **Validation**: Validate Jalali dates for accuracy.
- **Configurable**: Customize holidays and settings via a configuration file.
- **Extensible**: Future support for external APIs (e.g., Google Calendar) planned.

## Requirements
- PHP 8.1 or higher
- Laravel 9.0, 10.0, 11.0, or 12.0
- Composer

## Installation
1. Install the package via Composer:
   ```bash
   composer require picobaz/jalaliflow
   ```

2. Publish the configuration file (optional) to customize holidays:
   ```bash
   php artisan vendor:publish --tag=jalaliflow
   ```
   This will create a `config/jalaliflow.php` file.

3. Publish and run the migration (required for event scheduling):
   ```bash
   php artisan vendor:publish --tag=jalaliflow
   php artisan migrate
   ```
   This creates the `jalali_events` table for storing scheduled events.

4. (Optional) Add the facade to `config/app.php` (if not auto-registered):
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
    'holidays' => [
        '1404/01/01' => 'Norouz',
        '1404/01/02' => 'Norouz',
        // Add more holidays here
    ],
];
```

Modify these settings to change the date format, locale, timezone, or holiday list.

## Usage

### Converting Dates
Convert between Gregorian and Jalali calendars using the `JalaliFlow` facade.

```php
use PicoBaz\JalaliFlow\Facades\JalaliFlow;

// Convert Gregorian to Jalali
echo JalaliFlow::toJalali('2025-05-14'); // Output: 1404/02/24

// Convert Jalali to Gregorian
echo JalaliFlow::toGregorian('1404/02/24'); // Output: 2025-05-14

// Custom format
echo JalaliFlow::toJalali('2025-05-14', 'Y-m-d'); // Output: 1404-02-24
```

### Adding Dates
Add days, weeks, months, or years to a Jalali date, respecting variable month lengths and leap years.

```php
echo JalaliFlow::addDay('1404/02/24', 5);   // Output: 1404/02/29
echo JalaliFlow::addWeek('1404/02/24', 2);  // Output: 1404/03/08
echo JalaliFlow::addMonth('1404/02/24', 1); // Output: 1404/03/24
echo JalaliFlow::addYear('1404/02/24', 1);  // Output: 1405/02/24
```

### Subtracting Dates
Subtract days, weeks, months, or years from a Jalali date.

```php
echo JalaliFlow::subDay('1404/02/24', 5);   // Output: 1404/02/19
echo JalaliFlow::subWeek('1404/02/24', 2);  // Output: 1404/02/10
echo JalaliFlow::subMonth('1404/02/24', 1); // Output: 1404/01/24
echo JalaliFlow::subYear('1404/02/24', 1);  // Output: 1403/02/24
```

### Calculating Differences
Calculate the difference between two Jalali dates in days, weeks, months, or years.

```php
echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'day');   // Output: ~312
echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'week');  // Output: ~44.57
echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'month'); // Output: ~10.3
echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'year');  // Output: ~0.86
```

### Validating Jalali Dates
Check if a Jalali date is valid.

```php
echo JalaliFlow::validateJalaliDate('1404/02/24') ? 'Valid' : 'Invalid'; // Output: Valid
echo JalaliFlow::validateJalaliDate('1404/12/31') ? 'Valid' : 'Invalid'; // Output: Invalid
```
### Displaying Relative Jalali Dates

The `toRelativeJalali` method allows you to convert a Gregorian date to a relative Jalali string, such as "today", "yesterday", or "3 days ago". This is useful for user-friendly date displays in applications like chat or notification systems.

```php
use PicoBaz\JalaliFlow\Facades\JalaliFlow;

// Assuming today is 1404/02/24 (2025-05-22)
echo JalaliFlow::toRelativeJalali('2025-05-22'); // Output: امروز
echo JalaliFlow::toRelativeJalali('2025-05-21'); // Output: دیروز
echo JalaliFlow::toRelativeJalali('2025-05-24'); // Output: ۲ روز بعد
echo JalaliFlow::toRelativeJalali('2025-05-19'); // Output: ۳ روز پیش
echo JalaliFlow::toRelativeJalali('2025-05-19', 'Asia/Tehran', 'en'); // Output: 3 days ago
```

### Managing Holidays

`JalaliFlow` provides powerful methods to manage official and custom holidays in the Jalali calendar. These are perfect for scheduling, reservation systems, or any application needing to track holidays and working days.

- **Get holidays for a specific year:**
  Retrieve a list of official and custom holidays for a given Jalali year.
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  $holidays = JalaliFlow::getHolidays(1404);
  foreach ($holidays as $date => $description) {
      echo "$date: $description\n";
  }
  // Example output:
  // 1404/01/01: نوروز
  // 1404/01/02: نوروز
  // 1404/01/03: نوروز
  // 1404/01/04: نوروز
  // 1404/01/13: روز طبیعت
  // 1404/12/29: روز ملی شدن صنعت نفت
  ```

- **Check if a date is a holiday:**
  Determine if a specific Jalali date is a holiday (official or custom).
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      $date = '1404/01/01';
      echo JalaliFlow::isHoliday($date) ? "$date is a holiday" : "$date is not a holiday";
      // Output: 1404/01/01 is a holiday

      $date = '1404/02/24';
      echo JalaliFlow::isHoliday($date) ? "$date is a holiday" : "$date is not a holiday";
      // Output: 1404/02/24 is not a holiday
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

- **Add a custom holiday:**
  Add your own holidays to the calendar, such as company events or local celebrations.
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      JalaliFlow::addCustomHoliday('1404/06/15', 'Company Anniversary');
      echo JalaliFlow::isHoliday('1404/06/15') ? 'Custom holiday added' : 'Failed to add holiday';
      // Output: Custom holiday added

      // Verify in holidays list
      $holidays = JalaliFlow::getHolidays(1404);
      echo isset($holidays['1404/06/15']) ? "Found: {$holidays['1404/06/15']}" : 'Not found';
      // Output: Found: Company Anniversary
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

- **Check if a date is a working day:**
  Determine if a date is a working day (not a holiday and not a Friday, the official weekend in Iran).
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      $date = '1404/02/24';
      echo JalaliFlow::isWorkingDay($date) ? "$date is a working day" : "$date is not a working day";
      // Output: 1404/02/24 is a working day (assuming it's not a Friday)

      $date = '1404/01/01';
      echo JalaliFlow::isWorkingDay($date) ? "$date is a working day" : "$date is not a working day";
      // Output: 1404/01/01 is not a working day (Nowruz holiday)
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

**Note:** The `isWorkingDay` method considers Fridays as non-working days, as they are the official weekend in Iran. Ensure the date is in `Y/m/d` format (e.g., `1404/02/24`).
### Managing Events
Schedule events to run daily, weekly, monthly, or yearly with database persistence.

- **Create an Event:**
  Use `createEvent($name, $frequency, $startDate, $action)` to schedule an event.
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;
  use Illuminate\Support\Facades\DB;

  // Example: Add a daily record to the database
  JalaliFlow::createEvent(
      name: 'Add Daily Record',
      frequency: 'daily',
      startDate: '1404/02/24',
      action: function () {
          DB::table('records')->insert(['created_at' => now()]);
      }
  );

  // Example: Call a class method monthly
  JalaliFlow::createEvent(
      name: 'Monthly Report',
      frequency: 'monthly',
      startDate: '1404/02/24',
      action: 'App\Services\ReportService@generateMonthly'
  );
  ```

- **Calculate Next Run Date:**
  Use `getNextRunDate($currentDate, $frequency)` to calculate the next execution date.
  ```php
  echo JalaliFlow::getNextRunDate('1404/02/24', 'daily');   // Output: 1404/02/25
  echo JalaliFlow::getNextRunDate('1404/02/24', 'weekly');  // Output: 1404/03/01
  echo JalaliFlow::getNextRunDate('1404/02/24', 'monthly'); // Output: 1404/03/24
  echo JalaliFlow::getNextRunDate('1404/02/24', 'yearly');  // Output: 1405/02/24
  ```

- **Run Scheduled Events:**
  Execute scheduled events with the Artisan command:
  ```bash
  php artisan jalali:run-events
  ```

- **Setup Scheduler:**
  To run events automatically, configure the Laravel Scheduler to execute the `jalali:run-events` command daily.

  **For Laravel 11 and 12:**
  Add the following to `routes/console.php`:
  ```php
  use Illuminate\Support\Facades\Schedule;

  Schedule::command('jalali:run-events')->daily();
  ```

  **For Laravel 9 and 10:**
  Add the following to `app/Console/Kernel.php`:
  ```php
  protected function schedule(Schedule $schedule)
  {
      $schedule->command('jalali:run-events')->daily();
  }
  ```

  Ensure the Laravel Scheduler is running:
  ```bash
  php artisan schedule:work
  ```
  Or configure a cron job:
  ```bash
  * * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
  ```

### Eloquent Trait
Use the `JalaliDate` trait to automatically convert dates in Eloquent models.

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
### Managing Hijri (Islamic) Dates

`JalaliFlow` supports conversion to and from Hijri (Islamic) dates, as well as checking for major Islamic holidays. This is ideal for religious or cultural applications.

- **Convert to Hijri date:**
  Convert a Gregorian or Jalali date to a Hijri date.
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      echo JalaliFlow::toHijri('2025-05-22', 'gregorian', 'Y/m/d');
      // Output: 1446/11/14
      echo JalaliFlow::toHijri('1404/02/24', 'jalali', 'Y/m/d');
      // Output: 1446/11/14
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

- **Convert from Hijri date:**
  Convert a Hijri date to Gregorian or Jalali format.
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      echo JalaliFlow::fromHijri('1446/11/14', 'gregorian', 'Y-m-d');
      // Output: 2025-05-22
      echo JalaliFlow::fromHijri('1446/11/14', 'jalali', 'Y/m/d');
      // Output: 1404/02/24
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

- **Check for Islamic holidays:**
  Determine if a date is a major Islamic holiday (e.g., Ashura, Eid al-Fitr).
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  try {
      $date = '1404/04/25'; // Approximate date for Ashura 1446
      echo JalaliFlow::isIslamicHoliday($date, 'jalali') ? "$date is an Islamic holiday" : "$date is not an Islamic holiday";
      // Output: 1404/04/25 is an Islamic holiday (if Ashura)

      $date = '1404/02/24';
      echo JalaliFlow::isIslamicHoliday($date, 'jalali') ? "$date is an Islamic holiday" : "$date is not an Islamic holiday";
      // Output: 1404/02/24 is not an Islamic holiday
  } catch (Exception $e) {
      echo "Error: " . $e->getMessage();
  }
  ```

**Note:** Hijri date conversions are approximate due to the simplified algorithm. For precise conversions, consider integrating with an external API. Dates must be in `Y/m/d` format for Hijri and Jalali, or `Y-m-d` for Gregorian.
### Artisan Command
List holidays for a specific Jalali year:

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
> **Note**: Some advanced features (e.g., Google Calendar integration, dynamic holiday fetching) are planned for future releases and not yet implemented.

### Custom Date Formats
Override the default format in your code or configuration:

```php
echo JalaliFlow::toJalali('2025-05-14', 'Y-m-d H:i:s'); // Output: 1404-02-24 00:00:00
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

**Support**: For questions or issues, create a GitHub issue at [https://github.com/PicoBaz/JalaliFlow/issues](https://github.com/PicoBaz/JalaliFlow/issues) or contact us at [picobaz3@gmail.com](mailto:picobaz3@gmail.com).
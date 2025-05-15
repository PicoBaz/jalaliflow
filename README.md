# JalaliFlow

**JalaliFlow** is a Laravel package designed for advanced management of the Jalali (Persian) calendar. It provides utilities to convert dates between Gregorian and Jalali calendars, manipulate Jalali dates, calculate differences between dates, and check for Persian holidays. The package is lightweight and integrates seamlessly with Laravel applications.

## Features
- Convert Gregorian dates to Jalali and vice versa.
- Add or subtract days, weeks, months, or years to Jalali dates, respecting Persian calendar rules (e.g., variable month lengths and leap years).
- Calculate the difference between two Jalali dates in days, weeks, months, or years.
- Check if a Jalali date is a holiday (e.g., Norouz).
- Artisan command to list holidays for a given year.
- Trait to automatically convert model dates to Jalali format.

## Requirements
- PHP 8.1 or higher
- Laravel 9.x, 10.x, 11.x, or 12.x
- PHP Calendar extension (`ext-calendar`) enabled

## Installation
1. Install the package via Composer:
   ```bash
   composer require picobaz/jalaliflow
   ```

2. Publish the configuration file (optional):
   ```bash
   php artisan vendor:publish --tag=config
   ```
   This will create a `config/jalaliflow.php` file where you can customize settings, such as holiday lists.

3. (Optional) Add the `JalaliDate` trait to your Eloquent models to automatically convert dates to Jalali format:
   ```php
   use PicoBaz\JalaliFlow\Traits\JalaliDate;

   class Post extends Model
   {
       use JalaliDate;
   }
   ```

## Usage
Below are examples of the main features provided by **JalaliFlow**.

### Converting Dates
- **Gregorian to Jalali:**
  ```php
  use PicoBaz\JalaliFlow\Facades\JalaliFlow;

  echo JalaliFlow::toJalali('2025-05-14'); // Output: 1404/02/24
  echo JalaliFlow::toJalali('2025-05-14', 'Y-m-d'); // Output: 1404-02-24
  ```

- **Jalali to Gregorian:**
  ```php
  echo JalaliFlow::toGregorian('1404/02/24'); // Output: 2025-05-14
  ```

### Manipulating Jalali Dates
- **Add Days:**
  ```php
  echo JalaliFlow::addDay('1404/02/24', 5); // Output: 1404/02/29
  echo JalaliFlow::addDay('1404/12/29', 1); // Output: 1405/01/01 (handles year boundary)
  echo JalaliFlow::addDay('1403/12/29', 1); // Output: 1403/12/30 (handles leap year)
  ```

- **Add Weeks:**
  ```php
  echo JalaliFlow::addWeek('1404/02/24', 2); // Output: 1404/03/07
  ```

- **Add Months:**
  ```php
  echo JalaliFlow::addMonth('1404/02/24', 1); // Output: 1404/03/24
  echo JalaliFlow::addMonth('1404/12/01', 1); // Output: 1405/01/01
  ```

- **Add Years:**
  ```php
  echo JalaliFlow::addYear('1404/02/24', 1); // Output: 1405/02/24
  ```

**Note:** All manipulation methods handle Persian calendar rules, such as 31-day months (1-6), 30-day months (7-11), and 29/30-day Esfand in leap years. Negative numbers can be used to subtract (e.g., `addDay('1404/02/24', -5)`).

### Calculating Difference Between Dates
- **Difference in Days, Weeks, Months, or Years:**
  ```php
  echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'day');   // Output: ~312
  echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'week');  // Output: ~44.57
  echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'month'); // Output: ~10.3
  echo JalaliFlow::diff('1404/02/24', '1405/01/01', 'year');  // Output: ~0.86
  ```

**Note:** The `diff` method returns absolute values. Month and year calculations are approximate due to variable month lengths in the Persian calendar.

### Checking Holidays
- **Check if a date is a holiday:**
  ```php
  echo JalaliFlow::isHoliday('1404/01/01') ? 'Holiday' : 'Not a holiday'; // Output: Holiday (Norouz)
  ```

- **List holidays for a year using Artisan command:**
  ```bash
  php artisan jalali:holidays 1404
  ```
  This will display holidays defined in `config/jalaliflow.php`.

### Using the JalaliDate Trait
- Automatically convert model dates to Jalali:
  ```php
  $post = Post::create(['created_at' => '2025-05-14']);
  echo $post->jalali_created_at; // Output: 1404/02/24
  ```

## Configuration
After publishing the configuration file (`config/jalaliflow.php`), you can customize the list of holidays or other settings:
```php
return [
    'holidays' => [
        '1404/01/01' => 'Norouz',
        '1404/01/02' => 'Norouz',
        // Add more holidays
    ],
];
```

## Troubleshooting
- **Calendar Extension Missing:** Ensure the PHP Calendar extension is enabled by adding `extension=calendar` to your `php.ini` file and restarting your server.
- **Invalid Date Errors:** Ensure dates are in the correct format (`Y/m/d` for Jalali, `Y-m-d` for Gregorian).
- **Composer Issues:** Verify that your Laravel version (9.x, 10.x, 11.x, or 12.x) is compatible with the package.

## Contributing
Contributions are welcome! Please follow these steps:
1. Fork the repository: `https://github.com/PicoBaz/JalaliFlow`
2. Create a new branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m "Add your feature"`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a pull request.

## License
This package is open-source software licensed under the [MIT License](LICENSE).

## Contact
For questions or support, contact the maintainer:
- Name: PicoBaz
- Email: picobaz3@gmail.com
- GitHub: [PicoBaz](https://github.com/PicoBaz)
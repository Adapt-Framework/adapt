# Class `date`

**Inherits from:** [`base`](/docs/classes/base.md)

Provides easy to use date and time manipulations.  When constructing set the date that you would like to manipulate or leave out to use todays date.

## Table of contents
- [Constructing](#contructing)
- [Constants](#constants)
- [Properties](#properties)
    - [year](#year)
    - [month](#month)
    - [day](#day)
    - [hour](#hour)
    - [minute](#minute)
    - [second](#second)
- [Methods](#methods)
    - [set_date](#set_datedate--null-pattern--null)
    - [date](#datepattern--null)
    - [is_past](#is_pastinclude_time--false)
    - [is_future](#is_futureinclude_time--false)
    - [is_today](#is_today)
    - [is_working_day](#is_working_day)
    - [is_weekend](#is_weekend)
    - [is_weekday](#is_weekday)
    - [is_leap_year](#is_leap_year)
    - [days_in_month](#days_in_month)
    - [working_days_in_month](#working_days_in_month)
    - [goto_first_day](#goto_first_dayday--null)
    - [goto_last_day](#goto_last_dayday--null)
    - [goto_first_working_day](#goto_first_working_dayday--null)
    - [goto_last_working_day](#goto_last_working_dayday--null)
    - [goto_next_day](#goto_next_dayday_of_week)
    - [goto_second_day](#goto_second_dayday_of_week)
    - [goto_third_day](#goto_third_dayday_of_week)
    - [goto_second_working_day](#goto_second_working_dayday_of_week)
    - [goto_third_working_day](#goto_third_working_dayday_of_week)
    - [goto_days](#goto_daysdays)
    - [goto_working_days](#goto_working_daysdays)
    - [goto_months](#goto_monthsmonths)
    - [goto_years](#goto_years)
    - [goto_tomorrow](#goto_tomorrow)
    - [goto_yesterday](#goto_yesterday)
    - [goto_next_month](#goto_next_month)
    - [goto_last_month](#goto_last_month)
    - [goto_next_year](#goto_next_year)
    - [goto_last_year](#goto_last_year)
    - [goto_hours](#goto_hourshours)
    - [goto_minutes](#goto_minutesminutes)
    - [goto_seconds](#goto_secondsseconds)
    

## Constructing
### __construct(`$date = null`)
Constructs a new date objects and optionally sets the date.

#### Inputs:
- `$date` A string representing the date to set this object to.  When `null` todays date is used.

#### Example:
```php
$date = new date('2015-08-10');
```

## Constants
```php
const SUNDAY = 0;
const MONDAY = 1;
const TUESDAY = 2;
const WEDNESDAY = 3;
const THURSDAY = 4;
const FRIDAY = 5;
const SATURDAY = 6;
```

## Properties
### year
The year this object is set to.

--

### month
The month this object is set to.

--

### day
The day of the month this object is set to.

--

### hour
The hour this object is set to.

--

### minute
The minute this object is set to.

--

### second
The seconds this object is set to.


## Methods
### set_date(`$date = null`, `$pattern = null`)
Sets the date of this objectt.

#### Input:
- `$date` (Optional) The date to set this object too, leave `null` for todays date.
- `$pattern` (Optional) The date format of `$date`, if this is `null`, `date` attempts to workout the format itself.  Where possible you should provide this.

#### Example:
```php
$date = new date();

/* Set to todays date */
$date->set_date();

/* Set to 2015-08-10 */
$date->set_date('2015-08-10');

/* Set to 2015-08-10 in US format */
$date->set_date('08/10/2015', 'm/d/Y');

```

--

### date(`$pattern = null`)
Returns the date this object is currently set to.  When `$pattern` is `null` the number of seconds since the UNIX epoch is returned.

#### Inputs:
- `$pattern` (Optional) The format you would like the date returned in.  When `null` the number of seconds sinece the UNIX epoch is returned.

#### Returns:
- `Integer` or `String`.

#### Example:
```php

/* Create a new date */
$date = new date('2015-08-10');

/* Move the date forward to the next Monday */
$date->goto_next_day(date::MONDAY);

/* Output the date in the format d/m/Y */
print $date->date('d/m/Y');
```

--

### is_past(`$include_time = false`)
Is the date in the past?  Optionally when `$include_time` is set to `true` time is taken into account.

#### Inputs:
- `$include_time` (Optional) Should time be included when working out if the date and time is in the past.

#### Returns:
- A boolean indicating is the date is in the past.

#### Example:
```php
/* Create a new date object */
$date = new date('2015-08-10');

/* Is this date in the past? */
if ($date->is_past()){
    print "We are in the past";
}else{
    print "We are in the present or future";
}
```

--

### is_future(`$include_time = false`)
Checks if a date is in the future, optionally taking into account the time of day.

#### Input:
- `$include_time` (Optional) Should time be taken into account?

#### Returns:
- A boolean indicating if the date is in the future or not.

--

### is_today()
Is the current date todays date?

#### Returns:
- A boolean indicating if this is todays date.

--

### is_working_day()
Is the current date a working day?

#### Returns:
- A boolean indicating if the current date is a working day or not.

--

### is_weekend()
Is the current date a weekend?

#### Returns:
- A boolean indicating if the current date is a weekend or not.

--

### is_weekday()
Is the current date a week day?

#### Returns:
- A boolean indicating if the current date is a week day or not.

--

### is_leap_year()
Is the current year a leap year?

#### Returns:
- A boolean indicating if the current year is a leap year or not.

--

### days_in_month()
Returns an array of month numbers associated with the number of days in the month for the current year.  This array will always be the same unless the current years a leap year which will cause key '2' to be '29' instead of '28'.

#### Returns:
- `array(1 => 31, 2 => 28, 3 => 31, 4 => 30, ...)`

--

### working_days_in_month()
How many working days are in the current month?

#### Returns:
- `Integer` of the number of working days in the current month.

--

### goto_first_day(`$day = null`)
Moves the date object to the first day of the month.  When `$day` is specified the date is moved to the first `$day` of the month.

#### Inputs:
- `$day` (Optional) Move the date to the first `$day` of the month.

#### Example:
```php
/* Create a new date object */
$date = new date();

/* Move to the first day of the month */
$date->goto_first_day();

/* Move to the first Friday of the month */
$date->goto_first_day(date::FRIDAY);
```

--

### goto_last_day(`$day = null`)
Moves the date object to the last day of the month.  When `$day` is provided the date is moved to the last `$day` of the month.

#### Inputs:
- `$day` (Optional) Move the date to the last `$day` of the month.

#### Example:
```php
/* Create a new date object with todays date */
$date = new date();

/* Go to the last Wednesday of the current month */
$date->goto_last_day(date::WEDNESDAY);

/* Go to the last day of the month */
$date->goto_last_day();
```

--

### goto_first_working_day(`$day = null`)
Moves the date to the first working day in the month, optionally when `$day` is provided the date is moved to first working `$day` of the month.

#### Input:
- `$day` (Optional) Move the date to the first working `$day` of the month.

#### Example:
```php
/* Create a new date object set to today */
$date = new date();

/* Move to the first working day of the month */
$date->goto_first_working_day();

/* Move to the first working Monday of the month */
$date->goto_first_working_day(date::MONDAY);
```

--

### goto_last_working_day(`$day = null`)
Moves the date to the last working day in the month, optionally when `$day` is provided the date is moved to last working `$day` of the month.

#### Input:
- `$day` (Optional) Move the date to the last working `$day` of the month.

#### Example:
```php
/* Create a new date object set to today */
$date = new date();

/* Move to the last working day of the month */
$date->goto_last_working_day();

/* Move to the last working Tuesday of the month */
$date->goto_last_working_day(date::TUESDAY);
```

--

### goto_next_day(`$day_of_week`)
Moves the date to the next `$day_of_week`.

#### Inputs:
- `$day_of_week` The next day we should move too.

#### Example:
```php
/* Create a new date object set to today */
$date = new date();

/* Move to next Monday */
$date->goto_next_day(date::MONDAY);
```

--

### goto_second_day(`$day_of_week`)
Moves the date pointer to the second `$day_of_week` in the month.

#### Input:
- `$day_of_week` The day of the week to move the date to.

#### Example:
```php
/* Create new day object */
$date = new date('2015-08-01');

/* Move to the second Thursday in the month */
$date->goto_second_day(date::THURSDAY);
```

--

### goto_third_day(`$day_of_week`)
Moves the date pointer to the third `$day_of_week` in the month.

#### Input:
- `$day_of_week` The day of the week to move the date to.

#### Example:
```php
/* Create new day object */
$date = new date('2015-08-01');

/* Move to the second Thursday in the month */
$date->goto_third_day(date::THURSDAY);
```

--

### goto_second_working_day(`$day_of_week`)
Moves the date pointer to the second working `$day_of_week` in the month.

#### Input:
- `$day_of_week` The day of the week to move the date to.

#### Example:
```php
/* Create new day object */
$date = new date('2015-08-01');

/* Move to the second working Thrusday in the month */
$date->goto_second_working_day(date::THURSDAY);
```

--

### goto_third_working_day(`$day_of_week`)
Moves the date pointer to the third working `$day_of_week` in the month.

#### Input:
- `$day_of_week` The day of the week to move the date to.

#### Example:
```php
/* Create new day object */
$date = new date('2015-08-01');

/* Move to the third working Thrusday in the month */
$date->goto_third_working_day(date::THURSDAY);
```

--

### goto_days($days)
Moves the date forward or backwards by the number of `$days` provided.

#### Input:
- `$days` An integer representing the number of days to move forward, or when the value is negative the number of days to move back.

#### Example:
```php
/* Cretae a new date with todays date */
$date = date();

/* Go back 5 days */
$date->goto_days(-5);

/* Go forward 28 days */
$date->goto_days(28);
```

--

### goto_working_days($days)
Moves the date forward or backwards by the number of working `$days` provided.

#### Input:
- `$days` An integer representing the number of working days to move forward, or when the value is negative the number of working days to move back.

#### Example:
```php
/* Cretae a new date with todays date */
$date = date();

/* Go forward 5 working days */
$date->goto_working_days(5);

/* Because weekends will be skipped the new date will be seven days from now */
print $date->date('Y-m-d');

```

--

### goto_months($months)
Moves the date forward or backwards by the number of `$months` provided.

#### Input:
- `$months` An integer representing the number of months to move forward, or when the value is negative the number of months to move back.

#### Example:
```php
/* Cretae a new date with todays date */
$date = date();

/* Go back 3 months */
$date->goto_months(-3);
```

--

### goto_years($years)
Moves the date forward or backwards by the number of `$years` provided.

#### Input:
- `$years` An integer representing the number of years to move forward, or when the value is negative the number of years to move back.

#### Example:
```php
/* Cretae a new date with todays date */
$date = date();

/* Go forward 2 years */
$date->goto_years(2);
```

--

### goto_tomorrow()
Moves the date forward 1 day.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-08-10');

/* Go to tomorrow */
$date->goto_tomorrow();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2015-08-11"
 */
```

--

### goto_yesterday()
Moves the date backwards 1 day.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-08-10');

/* Go to yesterday */
$date->goto_yesterday();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2015-08-09"
 */
```

--

### goto_next_month()
Moves the date forward by 1 month.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-08-10');

/* Go to next month */
$date->goto_next_month();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2015-09-10"
 */
```

--

### goto_last_month()
Moves the date back by 1 month.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-08-10');

/* Go to next month */
$date->goto_next_month();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2015-09-10"
 */
```

--

### goto_next_year()
Moves the date forward 1 year.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-09-05');

/* Go to next year */
$date->goto_next_year();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2016-09-05"
 */
```

--

### goto_last_year()
Moves the date back 1 year.

#### Example:
```php
/* Cretae a new date  */
$date = date('2015-09-05');

/* Go to next year */
$date->goto_last_year();

/* Print out the new date */
print $date->date('Y-m-d');

/*
 * Prints "2014-09-05"
 */
```

--

### goto_hours(`$hours`)
Moves the date forward or back by the number of `$hours`.

#### Input:
- `$hours` An integer with the number of hours to move forward or back.

--

### goto_minutes(`$minutes`)
Moves the date forward or back by the number of `$minutes`.

#### Input:
- `$minutes` An integer with the number of minutes to move forward or back.

--

### goto_seconds(`$seconds`)
Moves the date forward or back by the number of `$seconds`.

#### Input:
- `$seconds` An integer with the number of seconds to move forward or back.

--

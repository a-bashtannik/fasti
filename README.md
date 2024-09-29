<p align="center">
<img height="100%" src="assets/cover.png" alt="Laravel Package Skeleton Logo"/>

</p>
<p align="center">
<a href="https://github.com/a-bashtannik/fasti/actions"><img src="https://github.com/a-bashtannik/fasti/actions/workflows/unit-tests.yml/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/a-bashtannik/fasti"><img src="https://img.shields.io/packagist/dt/a-bashtannik/fasti" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/a-bashtannik/fasti"><img src="https://img.shields.io/packagist/v/a-bashtannik/fasti" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/a-bashtannik/fasti"><img src="https://img.shields.io/packagist/l/a-bashtannik/fasti" alt="License"></a>
</p>

Fasti is a Laravel package that enables developers to precisely schedule task execution in the future. With Fasti, you can defer the launch of any `Job` for any period, specifying the time and date to the minute. At the designated time, Fasti retrieves the required Job from the repository and either executes it synchronously or dispatches it to a queue.

Unlike Laravel's powerful recurring task scheduler, Fasti focuses on scheduling individual tasks for specific times. It's user-friendly, allowing you to easily create, list, or cancel tasks on demand.


```php
$job = new SendGreetingEmail($user);

Fasti::schedule($job, '2024-12-31 23:59:59'); // Schedule a job for New Year's Eve 2024
```

## Installation
> [!NOTE]
> This package requires PHP 8.2+ and Laravel 11+

```
composer require a-bashtannik/fasti
```

Fasti is built with developers in mind.

## Usage

### Schedule your jobs using well-known Laravel patterns and interfaces.

Jobs are eligible for scheduling with Fasti if they implement a `handle()` method. At the scheduled time, Fasti executes your job synchronously within the cron job process thread, alongside any other cron jobs you've defined. To leverage Laravel's queuing system, simply implement the `ShouldQueue` interface on your job class. This allows Fasti to dispatch the job to Laravel's queue using the standard Bus facade behind the scenes.

```php
use \Bashtannik\Fasti\Facades\Fasti;

class SendGreetingEmail implements ShouldQueue
{
    public function handle()
    {
        // Send the email
    }
}

$job = new SendGreetingEmail($user);

Fasti::schedule($job, '2024-12-31 23:59:59'); // Push job to the queue for New Year's Eve 2024
```

This job will be executed synchronously at the specified time:
```php
use \Bashtannik\Fasti\Facades\Fasti;

class SendAlarmNotification
{
    public function handle()
    {
        // Send the notification
    }
}

$job = new SendAlarmNotification();

Fasti::schedule($job, '2025-01-01 08:00:00'); // Execute job synchronously on New Year's Day 2025
```

Fasti supports encrypted jobs if class implements `ShouldEncrypt` interface.

### Use tiny Eloquent model to store scheduled tasks

Fasti includes a lightweight Eloquent model that stores essential job information: ID, payload, and dates. While Fasti provides a service for convenient job management, you're not limited to it. In your controllers, you have the flexibility to interact directly with the Eloquent model using standard queries if you need more fine-grained control or custom operations.

```php
// Use the facade

use \Bashtannik\Fasti\Facades\Fasti;

Fasti::all(); // Retrieve all scheduled tasks
Fasti::scheduled($at); // Retrieve all tasks scheduled at a specific time
Fasti::cancel($id); // Cancel a scheduled task
Fasti::cancelled(); // Retrieve all canceled tasks
Fasti::find($id); // Retrieve a scheduled task by ID

// Or use the Eloquent model directly

use \Bashtannik\Fasti\Models\ScheduledJob;

ScheduledJob::where('scheduled_at', '=', '2024-12-31 23:59:59')->get();

```

### Testing

Test your scheduled jobs with ease using Fasti's `Fasti::fake()` method and built-in assertions.

```php
use \Bashtannik\Fasti\Facades\Fasti;

Fasti::fake();

Fasti::assertScheduled($job);
Fasti::assertNotScheduled($job);

Fasti::assertScheduledAt();
Fasti::assertNotScheduledAt();

Fasti::assertCancelled()

```

Fasti operates as a scheduling layer for your Laravel jobs, focusing solely on when to initiate them. It's important to note that Fasti doesn't manage job execution, queues, releases, attempts, or failures. 

Instead, when the scheduled time arrives, Fasti simply hands off the job to Laravel's default `Bus`.

It means you are free to use well-known Bus assertion methods shipped with Laravel. 

```php

Bus::assertDispatched($job);
Bus::assertNotDispatched($job);

// etc.

```
### Using custom models or storage to manage scheduled jobs

For simple applications, Fasti's built-in Eloquent model is sufficient. However, if you need to store scheduled jobs in a custom database table or use a different storage mechanism, Fasti provides a way to do so.

Define your own model implementing the `Bashtannik\Fasti\Contracts\SchedulableJob` interface. 

It's important to note that while your model is required to implement this interface, it doesn't overload it with new properties or methods. This approach allows Eloquent to operate normally. You are simply required to provide a standard set of fields in the model or data transfer object to ensure Fasti works smoothly and your IDE's type checking is happy.

```php
use Illuminate\Database\Eloquent\Model;
use Bashtannik\Fasti\Contracts\SchedulableJob;

class MyOwnModel extends Model implements SchedulableJob 
{
    // Your code
}

```

Create your own repository that implements the `FastiRepository` interface and bind it in your app service provider `register()` method.

```php
use Bashtannik\Fasti\Repositories\FastiRepository;
use App\Repositories\MyOwnRepository;

$this->app->bind(
    FastiScheduledJobsRepository::class,
    MyOwnRepository::class
);
```

Or use more context-aware approach and switch your repository on the fly.

```php
use \Bashtannik\Fasti\Facades\Fasti;

$repository = new MyOwnRepository(
    user: $user,
    company: $company,
);

Fasti::setRepository($repository);

```

You are done, now Fasti will use your custom repository to manage scheduled jobs.

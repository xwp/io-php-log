# "Logger" Library to log messages across the enterprise application. 

## Table of Contents
- [Description](#description)
- [Installation](#installation)
- [Usage](#usage)
    - [Basic Usage](#basic-usage)
    - [Advanced Usage](#advanced-usage)
    - [Runtime Channel](#runtime-channel)
    - [Custom File Channel](#custom-file-channel)
    - [Email Channel](#email-channel)
    - [WP_CLI Channel](#wp-cli-channel)
    - [Query Monitor Integration](#query-monitor-integration)
    - [Custom Channels](#custom-channels)
    - [Null Logger](#null-logger)
    - [Grouping Logs](#grouping-logs)
        - [Basic Usage](#basic-usage-1)
        - [Advanced Usage](#advanced-usage-1)

## Description
This library serves as a robust solution for logging messages throughout the enterprise application, fully aligning with the [PSR-3](https://www.php-fig.org/psr/psr-3/) standard. 
Its versatility shines particularly in scenarios where certain segments of your application are invoked through CRON jobs, or when managing multiple API providers leads to a complex web of interactions, making it challenging to track events effectively.

In such intricate situations, the logger emerges as a valuable asset. 
It allows you to record messages at various levels and provides the flexibility to log accompanying context alongside the message.

## Installation

Require this library as a development dependency for your project:

```bash
$ composer require xwp/log
```

## Usage

### Basic Usage

```php
<?php

// Include the Composer autoloader.
require_once __DIR__ . '/vendor/autoload.php';

use XWP\Log\Logger;
use XWP\Log\NullLogger;
use Psr\Log\LoggerInterface;

class Controller {
    // ...
 
    public function __construct(
        protected ?LoggerInterface $logger = null
    ) {
        $this->logger ??= new NullLogger();
    }
    
    // ...
    public function index(): Response {
        
        $response = wp_safe_remote_get( 'https://example.com' );
        
        if ( is_wp_error( $response ) ) {
        
            $this->logger->error( 'Failed to get response from example.com', [
                'error' => $response->get_error_message(),
            ] );
            
            return new Response( status: wp_remote_retrieve_response_code( $response ), headers: wp_remote_retrieve_headers( $response ) );
        }
        
        return new Response( 
            status: wp_remote_retrieve_response_code( $response ), 
            headers: wp_remote_retrieve_headers( $response ), 
            cookies: wp_remote_retrieve_cookies( $response ), 
            body: wp_remote_retrieve_body( $response )
        );
        // ...
    }
}
```

### Runtime Channel

Runtime channel allows you to log messages into the runtime [debug.log](https://wordpress.org/documentation/article/debugging-in-wordpress/#wp_debug_log) file, or in VIP case it's being logged into Runtime Log Stream.

```php
( new Controller( new Logger( new \XWP\Log\Channels\Runtime() ) ) )->index();
```

### Custom File Channel
Sometimes Runtime Channel might be not enough for your needs and you would love to track your logs in a separate file.
For such cases, you can use `File` channel.

```php
use XWP\Log\Channels\File;

( new Controller( new Logger( new File( '/path/to/log/file.log' ) ) ) )->index();
```

### Email Channel

Sometimes when something critical happens in your application you would love to get notified about it.
For such cases, you can use `Email` channel. Email channel will send you an email with the log message and context.
You can also specify level of the message that should trigger the email, by default it's `LogLevel::ERROR` and `LogLevel::CRITICAL`.

When log entry is created with level `LogLevel::ERROR` and `LogLevel::CRITICAL` (by default) or the ones you've specified, the email channel will get notify all recipients.

```php
use XWP\Log\Channels\Email;

$recipients = [ get_site_option( 'admin_email' ), /* ... */ ];

( new Controller( new Logger( new Email( $recipients ) ) ) )->index();

// Example with custom level.
( new Controller( new Logger( new Email( $recipients ), [ XWP\Log\LogLevel::CRITICAL ] ) ) )->index();
```

### WP_CLI Channel
When you use code that is being executed through WP-CLI, you might want to log messages to the WP-CLI console as well.
to accomplish that you can use `CLI` channel. 

```php
use XWP\Log\Channels;

( new Controller( new Logger( new Channels\Cli() ) ) )->index();
```

### Advanced Usage

When your application is growing, and you might want to have multiple channels to get your logs distributed.
For such cases, you can use `Distributor`.

The `Distributor` channel can composite multiple channels and distribute the log messages for each provided channel.

```php
    use XWP\Log\Channels;
    
    // ...

    ( 
        new Controller( 
            new Logger( 
                new Channels\Distributor(
                    new Channels\Runtime(),
                    new Channels\File( '/path/to/log/file.log' ),
                    new Channels\Email( [ get_site_option( 'admin_email' ), /* ... */ ] )
                )
            )
        ) 
    )->index();
```

### Query Monitor Integration
The library comes with a Query Monitor integration, which allows you to see the logs in the Query Monitor panel.
To be able to use it, you need to install [Query Monitor](https://wordpress.org/plugins/query-monitor/) plugin.

One thing to note, here that Query Monitor doesn't persist logs, you'll be able to see them only in runtime, 
such use case that involve CRON might not be the best fit for Query Monitor channel, but it still can be w/i the distributor, you just won't be able to see the logs in Query Monitor panel.

```php
use XWP\Log\Channels;

( 
    new Controller( 
        new Logger(
            new Channels\Distributor(
                new Channels\Runtime(),
                new Channels\QueryMonitor()
            )
        )
    ) 
)->index();
```

### Custom Channels
Even though the logger comes with a set of channels, you might want to create your own.
For example, you might want to log messages to Slack/SMS, or to a third-party service.

To create a custom channel, you just simply need to implement `XWP\Log\Contracts\Channel` interface, once you've done that
you can use it as any other channel, you can also place it as a part of the distributor.

### Null Logger
Sometimes your classes designed with a logger in mind, but you don't want to log anything, for such cases you can use `NullLogger`.
NullLogger is a simple implementation of [Null Object Pattern](https://www.oodesign.com/null-object-pattern) logger that does nothing, it's a no-op logger.

It's really comes handy for unit testing.

```php

use XWP\Log;
use Psr\Log\LoggerInterface;

class Controller {
    // ...
 
    public function __construct(
        protected ?LoggerInterface $logger = null
    ) {
        $this->logger ??= new Log\NullLogger();
    }
    
    // ...
}
```

## Grouping Logs

Sometimes you might want to group your logs, for example, you might want to group all logs that are related to a specific request.
For such cases, you can use `group_start` and `grou_end` methods on a `Logger` instance. 
Unfortunately, this feature is not supported by [PSR-3](https://www.php-fig.org/psr/psr-3/) standard, so you might need to use `XWP\Log\Contracts\Logger` interface instead of `Psr\Log\LoggerInterface`.

When you start a group by simple calling a `group_start` method, the logger will get prefixed all logs messages with the group name and share the context the group has been started with, until you call `group_end` method.
Upon `group_end` method call, the logger will remove the group name from the stack and remove the context the group has been provided with.

### Basic Usage

```php

use XWP\Log\Logger;

$logger = new Logger( new Log\Channels\Runtime() );

// ...
$logger->group_start( 'SPORT API PROVIDER', [ 'provider' => 'sportradar' ] );

$logger->info( 'Requesting data from sportradar API', [ 'endpoint' => 'https://api.sportradar.com/....' ] ); // Example: production.INFO SPORT API PROVIDER: Requesting data from sportradar API context: {"endpoint":"https://api.sportradar.com/....","provider":"sportradar"}

$logger->end_group();
```

### Advanced Usage
For advanced usage, you can also do nesting groups.

Important to know, when you start a group inside a group, the logger will prefix all logs messages with the latest group name, but it will share all contexts from all started group(s), 
you still can override the context values providing the log's context with the present key.

```php
$logger->group_start( 'SPORT', [ 'component' => 'sport' ] );

$logger->info('Start processing sport data'); // Example: production.INFO SPORT: Start processing sport data context: {"component":"sport"}

$logger->group_start( 'SportRadar', [ 'provider' => 'sportradar' ] );

$logger->info( 'Requesting data from sportradar API', [ 'endpoint' => 'https://api.sportradar.com/....' ] ); // Example: production.INFO SPORT: SportRadar: Requesting data from sportradar API context: {"endpoint":"https://api.sportradar.com/....","component":"sport","provider":"sportradar"}

// Overrides the context value for the key `provider` to `rotowire`
$logger->info( 'Fetched the info from RotoWire', [ 'provider' => 'rotowire' ] ); // Example: production.INFO SPORT: SportRadar: Fetched the info from RotoWire context: {"component":"sport","provider":"rotowire"}

// Will close the "SportRadar" group along with its context. 
$logger->end_group();

$logger->info( 'Requesting data from ESPN', [ 'endpoint' => 'https://example.com/api/v2/.....', 'provider' => 'espn' ] ); // Example: production.INFO SPORT: Requesting data from ESPN context: {"endpoint":"https://example.com/api/v2/.....","component":"sport","provider":"espn"}

// Will close the "SPORT" group along with its context.
$logger->end_group();

$logger->info( 'Finished processing' ); // Example: production.INFO Finished processing.
```

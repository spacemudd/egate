<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
<a href="https://github.com/laravel/framework/actions"><img src="https://github.com/laravel/framework/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/dt/laravel/framework" alt="Total Downloads"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/v/laravel/framework" alt="Latest Stable Version"></a>
<a href="https://packagist.org/packages/laravel/framework"><img src="https://img.shields.io/packagist/l/laravel/framework" alt="License"></a>
</p>

## About Laravel

Laravel is a web application framework with expressive, elegant syntax. We believe development must be an enjoyable and creative experience to be truly fulfilling. Laravel takes the pain out of development by easing common tasks used in many web projects, such as:

- [Simple, fast routing engine](https://laravel.com/docs/routing).
- [Powerful dependency injection container](https://laravel.com/docs/container).
- Multiple back-ends for [session](https://laravel.com/docs/session) and [cache](https://laravel.com/docs/cache) storage.
- Expressive, intuitive [database ORM](https://laravel.com/docs/eloquent).
- Database agnostic [schema migrations](https://laravel.com/docs/migrations).
- [Robust background job processing](https://laravel.com/docs/queues).
- [Real-time event broadcasting](https://laravel.com/docs/broadcasting).

Laravel is accessible, powerful, and provides tools required for large, robust applications.

## Learning Laravel

Laravel has the most extensive and thorough [documentation](https://laravel.com/docs) and video tutorial library of all modern web application frameworks, making it a breeze to get started with the framework.

You may also try the [Laravel Bootcamp](https://bootcamp.laravel.com), where you will be guided through building a modern Laravel application from scratch.

If you don't feel like reading, [Laracasts](https://laracasts.com) can help. Laracasts contains thousands of video tutorials on a range of topics including Laravel, modern PHP, unit testing, and JavaScript. Boost your skills by digging into our comprehensive video library.

## Laravel Sponsors

We would like to extend our thanks to the following sponsors for funding Laravel development. If you are interested in becoming a sponsor, please visit the [Laravel Partners program](https://partners.laravel.com).

### Premium Partners

- **[Vehikl](https://vehikl.com)**
- **[Tighten Co.](https://tighten.co)**
- **[Kirschbaum Development Group](https://kirschbaumdevelopment.com)**
- **[64 Robots](https://64robots.com)**
- **[Curotec](https://www.curotec.com/services/technologies/laravel)**
- **[DevSquad](https://devsquad.com/hire-laravel-developers)**
- **[Redberry](https://redberry.international/laravel-development)**
- **[Active Logic](https://activelogic.com)**

## Contributing

Thank you for considering contributing to the Laravel framework! The contribution guide can be found in the [Laravel documentation](https://laravel.com/docs/contributions).

## Code of Conduct

In order to ensure that the Laravel community is welcoming to all, please review and abide by the [Code of Conduct](https://laravel.com/docs/contributions#code-of-conduct).

## Security Vulnerabilities

If you discover a security vulnerability within Laravel, please send an e-mail to Taylor Otwell via [taylor@laravel.com](mailto:taylor@laravel.com). All security vulnerabilities will be promptly addressed.

## License

The Laravel framework is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

# eGate Access Control System

A Laravel-based cloud access control system that receives real-time requests from eGate Cloud Online Dual Channel Access Controllers (Plus B Edition).

## Features

- **Real-time Communication**: Handles HTTP protocol communication with eGate devices
- **Heartbeat Monitoring**: Receives device status updates every 5 seconds
- **Access Control**: Processes card swiping, QR codes, fingerprints, facial recognition, and more
- **Comprehensive Logging**: Stores all device communications with detailed metadata
- **Web Interface**: Beautiful dashboard for monitoring and analyzing access control events
- **Export Functionality**: Export logs to CSV format
- **Filtering & Search**: Advanced filtering by method, type, device, date range, and more

## Supported eGate Features

- **Card Types**: 
  - 0 = Card
  - 1 = QR Code
  - 2 = PIN
  - 3 = Button
  - 5 = Alarm
  - 9 = Base64 Data
  - 10 = Fingerprint
  - 11 = Finger Vein
  - 12 = RFID
  - 13/23 = Face
  - 28 = JSON
  - 30 = WG66
  - 31 = Social Security Card

- **Data Sources**:
  - 0 = WG Reader
  - 1 = RS232 Interface
  - 2 = 485 Interface
  - 5 = USB Interface
  - 6 = 232 Converter
  - 7 = Controller Button
  - 9 = Network

## Installation

1. **Clone the repository**
   ```bash
   git clone <repository-url>
   cd egate-eco
   ```

2. **Install dependencies**
   ```bash
   composer install
   npm install
   ```

3. **Environment setup**
   ```bash
   cp .env.example .env
   php artisan key:generate
   ```

4. **Database setup**
   ```bash
   php artisan migrate
   ```

5. **Start the development server**
   ```bash
   php artisan serve
   ```

## Configuration

### eGate Device Configuration

Configure your eGate device with the following settings:

- **Protocol**: HTTP
- **Server Address**: Your domain (e.g., `egate.alzeer-holding.com`)
- **Port**: 80 (or 443 for HTTPS)
- **URL Path**: `/data/Acs.aspx`
- **Communication Mode**: Client mode

### Access Control Rules

Edit the `evaluateAccess()` method in `app/Http/Controllers/EGateController.php` to implement your business logic:

```php
private function evaluateAccess(?string $type, ?string $card, ?string $reader): bool
{
    // Example: Allow access for card numbers starting with '123'
    if (str_starts_with($card, '123')) {
        return true;
    }
    
    // Example: Allow access for QR codes
    if (in_array($type, ['1', '9']) && !empty($card)) {
        return true;
    }
    
    // Example: Allow access for button requests
    if ($type === '3') {
        return true;
    }
    
    // Default: deny access
    return false;
}
```

## API Endpoints

### Main eGate Endpoint
- **URL**: `/data/Acs.aspx`
- **Methods**: GET, POST
- **Purpose**: Receives all eGate device communications

### Logs Endpoints
- **URL**: `/logs`
- **Purpose**: View and filter access control logs
- **Export**: `/logs/export` - Download logs as CSV
- **Details**: `/logs/{id}` - View detailed request information

## Usage

### 1. Device Integration

Once configured, your eGate device will automatically:
- Send heartbeat requests every 5 seconds
- Submit access control requests when someone swipes a card
- Receive responses with access decisions and display instructions

### 2. Monitoring

Visit `/logs` to:
- View real-time access control events
- Filter by method, type, device, or date range
- Export data for analysis
- View detailed request/response information

### 3. Testing

Use the test endpoints to simulate device requests:
- `/test/heartbeat` - Test heartbeat functionality
- `/test/access-control` - Test access control requests

## System Architecture

```
eGate Device → HTTP Request → Laravel App → Database Storage
                ↓
            Response with
            Access Decision
            Display Instructions
            Voice Commands
```

## Database Schema

The system stores comprehensive information about each request:

- **Basic Info**: Method, type, timestamp, device identifiers
- **Device Data**: MAC address, IP, serial number, firmware version
- **Environmental**: Temperature, humidity readings
- **Access Data**: Card numbers, QR codes, biometric data
- **Response**: Access decisions, display instructions, voice commands
- **Raw Data**: Complete request and response payloads

## Security Considerations

- All device communications are logged for audit purposes
- Implement proper access control rules in the `evaluateAccess()` method
- Consider implementing rate limiting for production use
- Use HTTPS in production environments
- Regularly review and clean old log data

## Troubleshooting

### Common Issues

1. **Device not connecting**
   - Verify server address and port configuration
   - Check firewall settings
   - Ensure URL path is correct (`/data/Acs.aspx`)

2. **No requests appearing in logs**
   - Verify device is in client mode
   - Check network connectivity
   - Review device configuration

3. **Access always denied**
   - Review `evaluateAccess()` method logic
   - Check request data in logs
   - Verify card/credential format

### Debug Mode

Enable Laravel debug mode in `.env`:
```env
APP_DEBUG=true
APP_LOG_LEVEL=debug
```

## Production Deployment

1. **Environment**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_LOG_LEVEL=error
   ```

2. **Database**
   - Use production database (MySQL/PostgreSQL)
   - Implement proper backup strategies
   - Consider log rotation and archiving

3. **Security**
   - Enable HTTPS
   - Implement rate limiting
   - Set up monitoring and alerting
   - Regular security updates

## Support

For issues and questions:
1. Check the logs at `/logs`
2. Review device configuration
3. Test with `/test/*` endpoints
4. Check Laravel logs in `storage/logs/`

## License

This project is open-sourced software licensed under the [MIT license](https://opensource.org/licenses/MIT).

<div dir="rtl">

<div align="center">

<img src="docs/logo.webp" alt="Variza" width="150">

# Variza PHP SDK

کیت توسعه PHP واریزا برای اتصال ساده و سریع فروشگاه‌ها و وب‌سایت‌ها به سرویس پرداخت واریزا.

</div>

<div align="center">

![PHP Version](https://img.shields.io/badge/PHP-8.1%2B-777BB4?style=flat-square&logo=php&logoColor=white)
![Tests](https://img.shields.io/github/actions/workflow/status/the6fallenangel/variza-php-sdk/ci.yml?label=CI&style=flat-square&logo=github)
![Packagist Version](https://img.shields.io/packagist/v/the6fallenangel/variza-php-sdk?style=flat-square&logo=packagist)
![Packagist Downloads](https://img.shields.io/packagist/dt/the6fallenangel/variza-php-sdk?style=flat-square&logo=packagist)
![License](https://img.shields.io/github/license/the6fallenangel/variza-php-sdk?style=flat-square&dummy=20260814)

</div>

این SDK امکانات موردنیاز برای ایجاد لینک پرداخت و دریافت و اعتبارسنجی اعلان‌های پرداخت (Webhook) را در اختیار شما قرار می‌دهد تا بتوانید پرداخت‌های کارت‌به‌کارت را به‌صورت خودکار در سیستم خود مدیریت کنید.

---

## ✨ ویژگی‌ها

- 🚀 **بدون وابستگی** — به کتابخانه‌های شخص ثالث وابسته نیست؛ در صورت در دسترس بودن cURL از آن استفاده می‌کند و در غیر این صورت به PHP Streams متکی می‌شود.
- 🔒 **اعتبارسنجی امن Webhook** — با استفاده از HMAC-SHA256 و مقایسه امن امضا (`hash_equals`).
- 🧩 **پشتیبانی از PHP 8.1** و نسخه‌های بالاتر.

---

## 📦 نصب

برای نصب SDK کافیست دستور زیر را اجرا کنید.

<div dir="ltr">

```bash
composer require the6fallenangel/variza-php-sdk
```

</div>

---

### ساخت لینک پرداخت

ابتدا یک نمونه از `VarizaClient` با توکن API خود ایجاد کنید. سپس با ارسال اطلاعات سفارش، لینک پرداخت را دریافت کرده و کاربر را به آن هدایت کنید.

<div dir="ltr">

```php
use The6FallenAngel\Variza\Expiry;
use The6FallenAngel\Variza\PayRequest;
use The6FallenAngel\Variza\VarizaClient;

$client = new VarizaClient(token: 'your-token');

$link = $client->pay(new PayRequest(
    amount: 50000,                    // amount in Toman (min 1000)
    returnUrl: 'https://shop.example/return',
    title: 'Order #123',              // optional
    cardLast4: '1234',                // optional — pick a specific card
    expiresIn: Expiry::OneHour,       // optional — link validity period
));

// redirect the user here
header('Location: '.$link->payUrl);
```

</div>

در مبلغ، واحد پول **تومان** است. در صورت نیاز می‌توانید برای لینک پرداخت عنوان سفارش، چهار رقم آخر کارت مقصد و مدت اعتبار لینک را نیز مشخص کنید. پس از ایجاد لینک، کافی است کاربر را به `payUrl` هدایت کنید.

### مدت اعتبار لینک پرداخت

برای تعیین مدت اعتبار لینک می‌توانید از مقدارهای آماده کلاس `Expiry` استفاده کنید:

| ثابت                    | مقدار   | توضیح      |
| ----------------------- | ------- | ---------- |
| `Expiry::ThirtyMinutes` | `30m`   | ۳۰ دقیقه   |
| `Expiry::OneHour`       | `1h`    | ۱ ساعت     |
| `Expiry::TwoHours`      | `2h`    | ۲ ساعت     |
| `Expiry::SixHours`      | `6h`    | ۶ ساعت     |
| `Expiry::OneDay`        | `1d`    | ۱ روز      |
| `Expiry::ThreeDays`     | `3d`    | ۳ روز      |
| `Expiry::OneWeek`       | `1w`    | ۱ هفته     |
| `Expiry::Never`         | `never` | بدون انقضا |

---

## 🔔 وب‌هوک

پس از تأیید موفق پرداخت، واریزا نتیجه پرداخت را از طریق یک درخواست `POST` به آدرس Webhook شما ارسال می‌کند.

بدنه درخواست به‌صورت JSON خام ارسال می‌شود و برای اطمینان از صحت درخواست، هدر `X-Webhook-Signature` نیز همراه آن قرار می‌گیرد. SDK امکان اعتبارسنجی این امضا را با استفاده از Webhook Secret در اختیار شما قرار می‌دهد.

<div dir="ltr">

```php
use The6FallenAngel\Variza\VarizaPaymentEvent;
use The6FallenAngel\Variza\VarizaWebhookVerifier;

$body = file_get_contents('php://input');                 // raw body — exactly as sent
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if (! VarizaWebhookVerifier::verify($body, $signature, 'your-webhook-secret')) {
    http_response_code(400);
    exit;
}

$event = VarizaPaymentEvent::fromJson($body);

if ($event->isPaymentPaid()) {
    // mark the order paid using $event->attemptCode (idempotent)
}

http_response_code(200);
```

</div>

> 💡 روش جایگزین: به‌جای `verify()` می‌توانید از `VarizaWebhookVerifier::assertValid()` استفاده کنید که در صورت نامعتبر بودن امضا، استثنای `InvalidSignatureException` پرتاب می‌کند.

### ⚠️ نکته مهم درباره Webhook

پردازش Webhook باید به‌صورت **idempotent** انجام شود؛ یعنی اگر یک رویداد بیش از یک بار دریافت شد، نباید باعث ثبت دوباره پرداخت یا تغییر اشتباه وضعیت سفارش شود.

واریزا در صورت دریافت پاسخ نامعتبر از سمت شما یا عدم دریافت پاسخ موفق، رویداد را دوباره ارسال می‌کند. تلاش‌های مجدد با فاصله‌های ۳۰، ۶۰، ۱۸۰ و ۶۰۰ ثانیه انجام می‌شوند و یک رویداد حداکثر ۵ بار ارسال خواهد شد.

به همین دلیل توصیه می‌شود پس از دریافت و اعتبارسنجی Webhook، در سریع‌ترین زمان ممکن پاسخ **HTTP 200** را برگردانید و پردازش‌های سنگین را به صف یا Job منتقل کنید.

---

## 🚨 مدیریت خطاها

| وضعیت HTTP | استثنا                | توضیح                   |
| ---------- | --------------------- | ----------------------- |
| `422`      | `ValidationException` | خطای اعتبارسنجی درخواست |
| `429`      | `RateLimitException`  | محدودیت نرخ درخواست     |
| سایر       | `ApiException`        | سایر خطاهای API         |

<div dir="ltr">

```php
use The6FallenAngel\Variza\Exception\RateLimitException;
use The6FallenAngel\Variza\Exception\ValidationException;

try {
    $client->pay($request);
} catch (ValidationException $e) {
    // invalid input fields
} catch (RateLimitException $e) {
    // rate limited — wait a bit
}
```

</div>

تمام این Exceptionها از `VarizaException` و در نهایت از `RuntimeException` ارث می‌برند و اطلاعاتی مانند کد وضعیت HTTP، خطاهای API و بدنه پاسخ را در اختیار شما قرار می‌دهند.

---

## 🧪 توسعه و اجرای تست‌ها

برای دریافت وابستگی‌های پروژه:

<div dir="ltr">

```bash
composer install
```

</div>

برای اجرای تست‌ها:

<div dir="ltr">

```bash
vendor/bin/phpunit
```

</div>

تست‌های پروژه به‌صورت خودکار در CI روی نسخه‌های مختلف PHP (8.1 تا 8.4) اجرا می‌شوند.

---

## 📄 مجوز

این پروژه تحت مجوز **MIT** منتشر شده است.

مستندات کامل API و راهنمای اتصال به واریزا را می‌توانید در صفحه مستندات فنی واریزا مشاهده کنید. برای آشنایی بیشتر با واریزا و قابلیت‌های آن به [variza.ir](https://variza.ir) مراجعه کنید.

</div>

---

## 🇬🇧 English

**Variza PHP SDK** is the PHP kit for connecting your stores and websites to the Variza payment service — create payment links and receive/verify payment webhooks to automate card-to-card payments in your system.

### ✨ Features

- 🚀 **Zero dependencies** — no third-party libraries; uses cURL when available, falls back to PHP Streams otherwise.
- 🔒 **Secure webhook verification** — HMAC-SHA256 with timing-safe comparison (`hash_equals`).
- 🧩 **Supports PHP 8.1** and above.

### 📦 Installation

```bash
composer require the6fallenangel/variza-php-sdk
```

### Create a payment link

Create a `VarizaClient` with your API token, send the order details, and redirect the customer to the returned link.

```php
use The6FallenAngel\Variza\Expiry;
use The6FallenAngel\Variza\PayRequest;
use The6FallenAngel\Variza\VarizaClient;

$client = new VarizaClient(token: 'your-token');

$link = $client->pay(new PayRequest(
    amount: 50000,                    // amount in Toman (min 1000)
    returnUrl: 'https://shop.example/return',
    title: 'Order #123',              // optional
    cardLast4: '1234',                // optional — pick a specific card
    expiresIn: Expiry::OneHour,       // optional — link validity period
));

// redirect the user here
header('Location: '.$link->payUrl);
```

The amount is in **Toman**. You can optionally set an order title, the last four digits of the destination card, and the link validity period. Once created, redirect the customer to `payUrl`.

### Payment link expiry

| Constant | Value | Description |
| --- | --- | --- |
| `Expiry::ThirtyMinutes` | `30m` | 30 minutes |
| `Expiry::OneHour` | `1h` | 1 hour |
| `Expiry::TwoHours` | `2h` | 2 hours |
| `Expiry::SixHours` | `6h` | 6 hours |
| `Expiry::OneDay` | `1d` | 1 day |
| `Expiry::ThreeDays` | `3d` | 3 days |
| `Expiry::OneWeek` | `1w` | 1 week |
| `Expiry::Never` | `never` | Never expires |

### Webhook

After a successful payment, Variza sends the result to your webhook URL via a `POST` request. The body is sent as raw JSON, along with an `X-Webhook-Signature` header. The SDK verifies the signature using your Webhook Secret.

```php
use The6FallenAngel\Variza\VarizaPaymentEvent;
use The6FallenAngel\Variza\VarizaWebhookVerifier;

$body = file_get_contents('php://input');                 // raw body — exactly as sent
$signature = $_SERVER['HTTP_X_WEBHOOK_SIGNATURE'] ?? '';

if (! VarizaWebhookVerifier::verify($body, $signature, 'your-webhook-secret')) {
    http_response_code(400);
    exit;
}

$event = VarizaPaymentEvent::fromJson($body);

if ($event->isPaymentPaid()) {
    // mark the order paid using $event->attemptCode (idempotent)
}

http_response_code(200);
```

> 💡 Alternative: use `VarizaWebhookVerifier::assertValid()` instead of `verify()` to throw an `InvalidSignatureException` on an invalid signature.

#### ⚠️ Important webhook notes

Webhook handling must be **idempotent** — receiving the same event more than once must not double-register a payment or wrongly change the order status.

If Variza receives an invalid response or no successful response, it re-delivers the event with retries at 30, 60, 180, and 600 seconds, up to 5 times. Return **HTTP 200** as soon as possible after receiving and verifying a webhook, and move heavy processing to a queue or job.

### Error handling

| HTTP Status | Exception | Description |
| --- | --- | --- |
| `422` | `ValidationException` | Request validation error |
| `429` | `RateLimitException` | Request rate limit reached |
| other | `ApiException` | Other API errors |

```php
use The6FallenAngel\Variza\Exception\RateLimitException;
use The6FallenAngel\Variza\Exception\ValidationException;

try {
    $client->pay($request);
} catch (ValidationException $e) {
    // invalid input fields
} catch (RateLimitException $e) {
    // rate limited — wait a bit
}
```

All exceptions extend `VarizaException`, which in turn extends `RuntimeException`, and carry the HTTP status code, API errors, and the response body.

### Development & tests

```bash
composer install
vendor/bin/phpunit
```

Tests run automatically in CI across PHP 8.1 – 8.4.

### License

Released under the **MIT** license. See the Variza developer docs for full API documentation, and visit [variza.ir](https://variza.ir) to learn more.
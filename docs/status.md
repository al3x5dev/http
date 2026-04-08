# Status
The `Status` enum represents the HTTP status codes in your application.

## Usage

You can use the `Status` enum to work with HTTP status codes in your application.
```php
use Mk4U\Http\Status;

Status::Ok;
// returns Status enum case

Status::NotFound->value;
// returns the HTTP status code for "Not Found" (404).

Status::Ok->message();
// returns "Ok"
```

### Method `Status::message()`.
This method gets the phrase for a specific status code via the enum case.
```php
Status::MethodNotAllowed->message();
// return "Method Not Allowed"

Status::Ok->message();
// return "Ok"
```

### Method `Status::phrase(int $code)`.
This static method returns the reason phrase for a given HTTP status code. Throws InvalidArgumentException if the code is not valid.

```php
Status::phrase(200);
// return "Ok"

Status::phrase(404);
// return "Not Found"

Status::phrase(500);
// return "Internal Server Error"
```

### Available Status Codes

The Status enum includes all standard HTTP status codes:

**1xx Informational:**
- `Continue` (100)
- `SwitchingProtocols` (101)
- `Processing` (102)
- `EarlyHints` (103)

**2xx Success:**
- `Ok` (200)
- `Created` (201)
- `Accepted` (202)
- `NonAuthoritativeInformation` (203)
- `NoContent` (204)
- `ResetContent` (205)
- `PartialContent` (206)
- `MultiStatus` (207)
- `AlreadyReported` (208)
- `ImUsed` (226)

**3xx Redirection:**
- `MultipleChoices` (300)
- `MovedPermanently` (301)
- `Found` (302)
- `SeeOther` (303)
- `NotModified` (304)
- `UseProxy` (305)
- `TemporaryRedirect` (307)
- `PermanentRedirect` (308)

**4xx Client Error:**
- `BadRequest` (400)
- `Unauthorized` (401)
- `PaymentRequired` (402)
- `Forbidden` (403)
- `NotFound` (404)
- `MethodNotAllowed` (405)
- `NotAcceptable` (406)
- `ProxyAuthenticationRequired` (407)
- `RequestTimeout` (408)
- `Conflict` (409)
- `Gone` (410)
- `LengthRequired` (411)
- `PreconditionFailed` (412)
- `ContentTooLarge` (413)
- `URITooLong` (414)
- `UnsupportedMediaType` (415)
- `RangeNotSatisfiable` (416)
- `ExpectationFailed` (417)
- `MisdirectedRequest` (421)
- `UnprocessableContent` (422)
- `Locked` (423)
- `FailedDependency` (424)
- `TooEarly` (425)
- `UpgradeRequired` (426)
- `PreconditionRequired` (428)
- `TooManyRequests` (429)
- `RequestHeaderFieldsTooLarge` (431)
- `UnavailableForLegalReasons` (451)

**5xx Server Error:**
- `InternalServerError` (500)
- `NotImplemented` (501)
- `BadGateway` (502)
- `ServiceUnavailable` (503)
- `GatewayTimeout` (504)
- `HTTPVersionNotSupported` (505)
- `VariantAlsoNegotiates` (506)
- `InsufficientStorage` (507)
- `LoopDetected` (508)
- `NotExtended` (510)
- `NetworkAuthenticationRequired` (511)

### Example: Using with Response

```php
use Mk4U\Http\Response;
use Mk4U\Http\Status;

$response = Response::json(['error' => 'Not found'], Status::NotFound);
$response->getStatusCode(); // 404

// Or using array format
$response = Response::json(['error' => 'Not found'], [404, 'Not Found']);
```
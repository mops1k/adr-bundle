## Documentation

### Action
Example action:
```php
<?php

namespace App\Action\User;

#[\Symfony\Component\Routing\Attribute\Route('/user/{id}')]
#[\AdrBundle\Attribute\Responder(\AdrBundle\Response\Responder\JsonResponder::class)]
class Info
{
    public function __construct(
        private UserRepository $userRepository
    ) {}

    public function __invoke(int $id): array
    {
        $user = $this->userRepository->find($id);

        return ['user' => $user]
    }
}
```

In example above we create independent action and make it's routing and responder configuration. When we make request
to path http://localhost/user/5 - action will find a User and Responder will represent it's data to json response.
However, we still can use this action in another services, like commands, but if your action has a dependencies, you
need to declare action as `public: true`:
```yaml
services:
  App\Action\User\Info:
      public: true
```

Tip: action __invoke arguments has resolving thru symfony controller argument resolver, so u can use it to resolve
you're data to objects or anything else.

### Responders
Bundle have list of most usage responders:
- [DefaultResponder](../src/Response/Responder/DefaultResponder.php) - expecting action return string or null
- [FileResponder](../src/Response/Responder/FileResponder.php) - expecting action return file path as string or \SplFileInfo
- [JsonResponder](../src/Response/Responder/JsonResponder.php) - expecting action return array or serializable object
(if symfony/serializer is used). Optional can have attribute `AdrBundle\Attribute\ContentDispositionType`
- [RedirectResponder](../src/Response/Responder/RedirectResponder.php) - expecting action return url string
- [TemplatingResponder](../src/Response/Responder/TemplatingResponder.php) - expecting action return array and have
additional `Symfony\Bridge\Twig\Attribute\Template` attribute

### Custom responder
To create your own custom responder you must implement `\AdrBundle\Response\Contract\ResponderInterface` and add service
tag `adr.action.responder`.

Example responder:
```php
<?php

namespace App\Responder;

use AdrBundle\Response\Contract\ResponderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;

class ApiResponder implements ResponderInterface
{
    public function __invoke(mixed $data, array $attributes, array $responseArguments): JsonResponse
    {
        if (!is_array($data)) {
            throw new \RuntimeException(\sprintf('Action must return array, %s given.', gettype($data)));
        }

        return new JsonResponse(['data' => $data], ...$responseArguments);
    }
}

```

Example yaml configuration:
```yaml
services:
    App\Responder\ApiResponder:
        tags: ['adr.action.responder']
```

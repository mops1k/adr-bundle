<?php

namespace AdrBundle\Response\Responder;

use AdrBundle\Attribute\ContentDispositionType;
use AdrBundle\Response\Contract\ResponderInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

class FileResponder implements ResponderInterface
{
    public function __invoke(mixed $data, array $attributes, array $responseArguments): Response
    {
        if (!is_string($data) || !$data instanceof \SplFileInfo) {
            throw new \RuntimeException(\sprintf(
                'Action result must be a string or instance of %s, %s given.',
                \SplFileInfo::class,
                gettype($data)
            ));
        }

        $response = new BinaryFileResponse($data);
        $disposition = ResponseHeaderBag::DISPOSITION_ATTACHMENT;
        if (\array_key_exists(ContentDispositionType::class, $attributes)) {
            /** @var ContentDispositionType $contentDispositionTypeAttribute */
            $contentDispositionTypeAttribute = $attributes[ContentDispositionType::class];
            $disposition = $contentDispositionTypeAttribute->type;
        }
        $response->setContentDisposition($disposition, $response->getFile()->getFilename());

        return $response;
    }
}

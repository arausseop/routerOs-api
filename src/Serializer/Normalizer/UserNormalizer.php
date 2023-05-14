<?php

namespace App\Serializer\Normalizer;

use App\Entity\Main\User;
use App\Entity\Main\FieldImage;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\UrlHelper;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class UserNormalizer implements NormalizerInterface, CacheableSupportsMethodInterface
{

    // private $normalizer;
    private $urlHelper;

    public function __construct(
        // ObjectNormalizer $normalizer,
        UrlHelper $urlHelper
    ) {
        // $this->normalizer = $normalizer;
        $this->urlHelper = $urlHelper;
    }

    public function normalize($user, string $format = null, array $context = []): array
    {
        // $data = $this->normalizer->normalize($fieldImage, $format, $context);
        $data = $this->normalizer->normalize($user, $format, $context);

        if (in_array('users', $context['groups'])) {
            // dd($data);
            if (!empty($user->getAvatar())) {
                $data['avatar'] = $this->urlHelper->getAbsoluteUrl('/storage/default/' . $user->getAvatar());
            }
        }
        return $data;
    }

    public function supportsNormalization($data, string $format = null, array $context = []): bool
    {
        return $data instanceof User;
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return true;
    }

    public function setBaseNormalizer($normalizer)
    {
        $this->normalizer = $normalizer;
    }
}

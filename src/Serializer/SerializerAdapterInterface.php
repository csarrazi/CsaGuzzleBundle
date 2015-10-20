<?php

/*
 * This file is part of the CsaGuzzleBundle package
 *
 * (c) Charles Sarrazin <charles@sarraz.in>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Csa\Bundle\GuzzleBundle\Serializer;

interface SerializerAdapterInterface
{
    /**
     * Serializes data in the appropriate format.
     *
     * @param mixed  $data    any data
     * @param string $format  format name
     * @param mixed  $context options normalizers/encoders have access to
     *
     * @return string
     */
    public function serialize($data, $format, $context = null);

    /**
     * Deserializes data into the given type.
     *
     * @param mixed  $data
     * @param string $type
     * @param string $format
     * @param mixed  $context
     *
     * @return mixed
     */
    public function deserialize($data, $type, $format, $context = null);
}

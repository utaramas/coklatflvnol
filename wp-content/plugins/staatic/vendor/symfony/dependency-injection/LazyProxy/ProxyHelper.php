<?php

namespace Staatic\Vendor\Symfony\Component\DependencyInjection\LazyProxy;

class ProxyHelper
{
    /**
     * @param \ReflectionFunctionAbstract $r
     * @param \ReflectionParameter|null $p
     * @param bool $noBuiltin
     * @return string|null
     */
    public static function getTypeHint($r, $p = null, $noBuiltin = \false)
    {
        if ($p instanceof \ReflectionParameter) {
            $type = $p->getType();
        } else {
            $type = $r->getReturnType();
        }
        if (!$type) {
            return null;
        }
        $types = [];
        foreach ($type instanceof \ReflectionUnionType ? $type->getTypes() : [$type] as $type) {
            $name = $type instanceof \ReflectionNamedType ? $type->getName() : (string) $type;
            if ($type->isBuiltin()) {
                if (!$noBuiltin) {
                    $types[] = $name;
                }
                continue;
            }
            $lcName = \strtolower($name);
            $prefix = $noBuiltin ? '' : '\\';
            if ('self' !== $lcName && 'parent' !== $lcName) {
                $types[] = '' !== $prefix ? $prefix . $name : $name;
                continue;
            }
            if (!$r instanceof \ReflectionMethod) {
                continue;
            }
            if ('self' === $lcName) {
                $types[] = $prefix . $r->getDeclaringClass()->name;
            } else {
                $types[] = ($parent = $r->getDeclaringClass()->getParentClass()) ? $prefix . $parent->name : null;
            }
        }
        return $types ? \implode('|', $types) : null;
    }
}

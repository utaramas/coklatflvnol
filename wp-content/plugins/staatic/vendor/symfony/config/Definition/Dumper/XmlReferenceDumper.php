<?php

namespace Staatic\Vendor\Symfony\Component\Config\Definition\Dumper;

use Staatic\Vendor\Symfony\Component\Config\Definition\ArrayNode;
use Staatic\Vendor\Symfony\Component\Config\Definition\BaseNode;
use Staatic\Vendor\Symfony\Component\Config\Definition\ConfigurationInterface;
use Staatic\Vendor\Symfony\Component\Config\Definition\EnumNode;
use Staatic\Vendor\Symfony\Component\Config\Definition\NodeInterface;
use Staatic\Vendor\Symfony\Component\Config\Definition\PrototypedArrayNode;
class XmlReferenceDumper
{
    private $reference;
    /**
     * @param ConfigurationInterface $configuration
     * @param string|null $namespace
     */
    public function dump($configuration, $namespace = null)
    {
        return $this->dumpNode($configuration->getConfigTreeBuilder()->buildTree(), $namespace);
    }
    /**
     * @param NodeInterface $node
     * @param string|null $namespace
     */
    public function dumpNode($node, $namespace = null)
    {
        $this->reference = '';
        $this->writeNode($node, 0, \true, $namespace);
        $ref = $this->reference;
        $this->reference = null;
        return $ref;
    }
    private function writeNode(NodeInterface $node, int $depth = 0, bool $root = \false, string $namespace = null)
    {
        $rootName = $root ? 'config' : $node->getName();
        $rootNamespace = $namespace ?: ($root ? 'http://example.org/schema/dic/' . $node->getName() : null);
        if ($node->getParent()) {
            $remapping = \array_filter($node->getParent()->getXmlRemappings(), function ($mapping) use($rootName) {
                return $rootName === $mapping[1];
            });
            if (\count($remapping)) {
                list($singular) = \current($remapping);
                $rootName = $singular;
            }
        }
        $rootName = \str_replace('_', '-', $rootName);
        $rootAttributes = [];
        $rootAttributeComments = [];
        $rootChildren = [];
        $rootComments = [];
        if ($node instanceof ArrayNode) {
            $children = $node->getChildren();
            if ($rootInfo = $node->getInfo()) {
                $rootComments[] = $rootInfo;
            }
            if ($rootNamespace) {
                $rootComments[] = 'Namespace: ' . $rootNamespace;
            }
            if ($node instanceof PrototypedArrayNode) {
                $prototype = $node->getPrototype();
                $info = 'prototype';
                if (null !== $prototype->getInfo()) {
                    $info .= ': ' . $prototype->getInfo();
                }
                \array_unshift($rootComments, $info);
                if ($key = $node->getKeyAttribute()) {
                    $rootAttributes[$key] = \str_replace('-', ' ', $rootName) . ' ' . $key;
                }
                if ($prototype instanceof PrototypedArrayNode) {
                    $prototype->setName($key ?? '');
                    $children = [$key => $prototype];
                } elseif ($prototype instanceof ArrayNode) {
                    $children = $prototype->getChildren();
                } else {
                    if ($prototype->hasDefaultValue()) {
                        $prototypeValue = $prototype->getDefaultValue();
                    } else {
                        switch (\get_class($prototype)) {
                            case 'Symfony\\Component\\Config\\Definition\\ScalarNode':
                                $prototypeValue = 'scalar value';
                                break;
                            case 'Symfony\\Component\\Config\\Definition\\FloatNode':
                            case 'Symfony\\Component\\Config\\Definition\\IntegerNode':
                                $prototypeValue = 'numeric value';
                                break;
                            case 'Symfony\\Component\\Config\\Definition\\BooleanNode':
                                $prototypeValue = 'true|false';
                                break;
                            case 'Symfony\\Component\\Config\\Definition\\EnumNode':
                                $prototypeValue = \implode('|', \array_map('json_encode', $prototype->getValues()));
                                break;
                            default:
                                $prototypeValue = 'value';
                        }
                    }
                }
            }
            foreach ($children as $child) {
                if ($child instanceof ArrayNode) {
                    $rootChildren[] = $child;
                    continue;
                }
                $name = \str_replace('_', '-', $child->getName());
                $value = '%%%%not_defined%%%%';
                $comments = [];
                if ($child instanceof BaseNode && ($info = $child->getInfo())) {
                    $comments[] = $info;
                }
                if ($child instanceof BaseNode && ($example = $child->getExample())) {
                    $comments[] = 'Example: ' . $example;
                }
                if ($child->isRequired()) {
                    $comments[] = 'Required';
                }
                if ($child instanceof BaseNode && $child->isDeprecated()) {
                    $deprecation = $child->getDeprecation($child->getName(), $node->getPath());
                    $comments[] = \sprintf('Deprecated (%s)', ($deprecation['package'] || $deprecation['version'] ? "Since {$deprecation['package']} {$deprecation['version']}: " : '') . $deprecation['message']);
                }
                if ($child instanceof EnumNode) {
                    $comments[] = 'One of ' . \implode('; ', \array_map('json_encode', $child->getValues()));
                }
                if (\count($comments)) {
                    $rootAttributeComments[$name] = \implode(";\n", $comments);
                }
                if ($child->hasDefaultValue()) {
                    $value = $child->getDefaultValue();
                }
                $rootAttributes[$name] = $value;
            }
        }
        if (\count($rootComments)) {
            foreach ($rootComments as $comment) {
                $this->writeLine('<!-- ' . $comment . ' -->', $depth);
            }
        }
        if (\count($rootAttributeComments)) {
            foreach ($rootAttributeComments as $attrName => $comment) {
                $commentDepth = $depth + 4 + \strlen($attrName) + 2;
                $commentLines = \explode("\n", $comment);
                $multiline = \count($commentLines) > 1;
                $comment = \implode(\PHP_EOL . \str_repeat(' ', $commentDepth), $commentLines);
                if ($multiline) {
                    $this->writeLine('<!--', $depth);
                    $this->writeLine($attrName . ': ' . $comment, $depth + 4);
                    $this->writeLine('-->', $depth);
                } else {
                    $this->writeLine('<!-- ' . $attrName . ': ' . $comment . ' -->', $depth);
                }
            }
        }
        $rootIsVariablePrototype = isset($prototypeValue);
        $rootIsEmptyTag = 0 === \count($rootChildren) && !$rootIsVariablePrototype;
        $rootOpenTag = '<' . $rootName;
        if (1 >= ($attributesCount = \count($rootAttributes))) {
            if (1 === $attributesCount) {
                $rootOpenTag .= \sprintf(' %s="%s"', \current(\array_keys($rootAttributes)), $this->writeValue(\current($rootAttributes)));
            }
            $rootOpenTag .= $rootIsEmptyTag ? ' />' : '>';
            if ($rootIsVariablePrototype) {
                $rootOpenTag .= $prototypeValue . '</' . $rootName . '>';
            }
            $this->writeLine($rootOpenTag, $depth);
        } else {
            $this->writeLine($rootOpenTag, $depth);
            $i = 1;
            foreach ($rootAttributes as $attrName => $attrValue) {
                $attr = \sprintf('%s="%s"', $attrName, $this->writeValue($attrValue));
                $this->writeLine($attr, $depth + 4);
                if ($attributesCount === $i++) {
                    $this->writeLine($rootIsEmptyTag ? '/>' : '>', $depth);
                    if ($rootIsVariablePrototype) {
                        $rootOpenTag .= $prototypeValue . '</' . $rootName . '>';
                    }
                }
            }
        }
        foreach ($rootChildren as $child) {
            $this->writeLine('');
            $this->writeNode($child, $depth + 4);
        }
        if (!$rootIsEmptyTag && !$rootIsVariablePrototype) {
            $this->writeLine('');
            $rootEndTag = '</' . $rootName . '>';
            $this->writeLine($rootEndTag, $depth);
        }
    }
    private function writeLine(string $text, int $indent = 0)
    {
        $indent = \strlen($text) + $indent;
        $format = '%' . $indent . 's';
        $this->reference .= \sprintf($format, $text) . \PHP_EOL;
    }
    private function writeValue($value) : string
    {
        if ('%%%%not_defined%%%%' === $value) {
            return '';
        }
        if (\is_string($value) || \is_numeric($value)) {
            return $value;
        }
        if (\false === $value) {
            return 'false';
        }
        if (\true === $value) {
            return 'true';
        }
        if (null === $value) {
            return 'null';
        }
        if (empty($value)) {
            return '';
        }
        if (\is_array($value)) {
            return \implode(',', $value);
        }
        return '';
    }
}

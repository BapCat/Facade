@php declare(strict_types=1);

<?php

/**
 * @var string $binding
 * @var string $ioc
 * @var ReflectionClass $reflect
 * @var ReflectionMethod[] $methods
 * @var ReflectionMethod $method
 * @var string[] $imports
 */

?>

use BapCat\Facade\Facade;

@each($imports as $import)
use {! $import !};
@endeach

class {! $name !} extends Facade {
@each($reflect->getConstants() as $const, $val)
  public const {! $const !} = {! $binding !}::{! $const !};
@endeach

  /** @var string $ioc */
  protected static $ioc = {! $ioc !}::class;

  /** @var string $binding */
  protected static $binding = {! $binding !}::class;
@each($methods as $method)

@if(stripos($method->getDocComment(), '@inheritdoc') === false)
  {! $method->getDocComment() !}
@else
<?php

$parent = $reflect->getParentClass();

if($parent !== false) {
  try {
    $parentMethod = $parent->getMethod($method->getName());
    $docs = $parentMethod->getDocComment();
  } catch(ReflectionException $ignored) { }
}

if(empty($docs)) {
  foreach($reflect->getInterfaces() as $interface) {
    $interfaceMethod = $interface->getMethod($method->getName());
    $docs = $interfaceMethod->getDocComment();
  }
}

?>

@if(!empty($docs))
  {! $docs !}
@endif
@endif
<?php

$params = [];
$args = [];

foreach($method->getParameters() as $param) {
  $p = '$' . $param->getName();
  $args[] = $p;

  if($param->isPassedByReference()) {
    $p = "&$p";
  }

  if($param->isVariadic()) {
    $p = "...$p";
  }

  if($param->hasType()) {
    if($param->getType() instanceof ReflectionUnionType) {
      $types = $param->getType()->getTypes();
    } else {
      $types = [$param->getType()];
    }

    $strs = [];
    foreach($types as $type) {
      $strs[] = $type->getName();
    }

    $paramList = implode('|', $strs);

    if($param->allowsNull()) {
      if(!($param->getType() instanceof ReflectionUnionType)) {
        $paramList = "?$paramList";
      }
    }

    $p = "$paramList $p";
  }

  if($param->isDefaultValueAvailable()) {
    $type = $param->getDefaultValue();
    // Default values for primitives may have constant, primitive values - hence the var_export
    $p .= ' = ' . ($type instanceof ReflectionNamedType ? $type->getName() : var_export($type, true));
  }

  $params[] = $p;
}

if(!$method->hasReturnType()) {
  $returnType = '';
} else {
  if($method->getReturnType() instanceof ReflectionUnionType) {
    $types = $method->getReturnType()->getTypes();
  } else {
    $types = [$method->getReturnType()];
  }

  $returnTypes = [];
  foreach($types as $type) {
    $returnTypes[] = $type->getName();
  }

  $returnType = implode('|', $returnTypes);

  if($returnType === 'self') {
    $returnType = $binding;
  }

  if($method->getReturnType()->allowsNull() && $returnType !== 'mixed') {
    if(!($method->getReturnType() instanceof ReflectionUnionType)) {
      $returnType = "?$returnType";
    }
  }

  $returnType = ": $returnType";
}

?>

  public static function {! $method->getName() !}({! implode(', ', $params) !}){! $returnType !} {
@if($method->hasReturnType() && !($method->getReturnType() instanceof ReflectionUnionType) && $method->getReturnType()->getName() === 'void')
    self::inst()->{! $method->getName() !}({! implode(', ', $args) !});
@else
    return self::inst()->{! $method->getName() !}({! implode(', ', $args) !});
@endif
  }
@endeach
}

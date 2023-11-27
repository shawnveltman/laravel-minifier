<?php

namespace Shawnveltman\LaravelMinifier\CodeParser;

use Illuminate\Support\Facades\Log;
use ReflectionClass;
use ReflectionMethod;
use SplFileObject;

class MethodAnalysisService
{
    private array $requiredMethods = [];

    public function analyze_class($className, $methodName = null): array
    {
        $reflection = $this->get_reflection_class($className);

        $this->requiredMethods[$reflection->getName()] = [];

        if ($methodName)
        {
            $methods = $this->analyze_specific_method($reflection, $methodName, $className);
        } else
        {
            $methods = $this->analyze_all_methods($reflection);
        }

        $useStatements = $this->get_use_statements($reflection);

        foreach ($methods as $method)
        {
            $calls = $this->analyze_method($method, $useStatements);

            foreach ($calls as $call)
            {
                if (!array_key_exists($call['class'], $this->requiredMethods) ||
                    !in_array($call['method'], $this->requiredMethods[$call['class']]))
                {
                    $this->requiredMethods[$call['class']][] = $call['method'];
                }
                $this->analyze_class($call['class'], $call['method']);
            }
        }

        return $this->requiredMethods;
    }

    protected function is_allowed_namespace($namespace): bool
    {
        $allowedNamespaces = config('minifier.namespaces', ['App']);
        foreach ($allowedNamespaces as $allowedNamespace)
        {
            if (strpos($namespace, $allowedNamespace) === 0)
            {
                return true;
            }
        }
        return false;
    }

    public function get_own_methods(ReflectionClass $reflection): array
    {
        $own_methods = array_filter($reflection->getMethods(ReflectionMethod::IS_PUBLIC | ReflectionMethod::IS_PROTECTED | ReflectionMethod::IS_PRIVATE), function ($method) use ($reflection) {
            return $method->getDeclaringClass()->getName() === $reflection->getName();
        });

        $methodNames = array_map(fn($method) => $method->getName(), $own_methods);
        error_log("Own Methods in class " . $reflection->getName() . ": " . implode(', ', $methodNames));

        return $own_methods;

    }

    public function analyze_method(ReflectionMethod $method, array $useStatements): array
    {
        $methodBody  = $this->get_method_body($method);
        $methodCalls = [];

        if (preg_match_all('/\b([a-zA-Z0-9_]+)\??->([a-zA-Z0-9_]+)\(/', $methodBody, $matches, PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                $calledMethodName = $match[2];
                // Find the class that contains the method
                $className = $this->find_class_for_method($calledMethodName, $useStatements);

                if ($className)
                {
                    $methodCalls[] = ['class' => $className, 'method' => $calledMethodName];
                }
            }
        }

        return $methodCalls;
    }

    public function find_class_for_method($methodName, array $useStatements)
    {
        foreach ($useStatements as $shortName => $fullClassName)
        {
            if (method_exists($fullClassName, $methodName))
            {
                return $fullClassName;
            }
        }

        return null;
    }

    public function get_method_body(ReflectionMethod $method): string
    {
        $filePath  = $method->getFileName();
        $startLine = $method->getStartLine() - 1; // Adjust for zero-based indexing
        $endLine   = $method->getEndLine();

        $file = new SplFileObject($filePath);
        $file->seek($startLine);

        $methodBody = '';

        while ($file->key() < $endLine)
        {
            $methodBody .= $file->current();
            $file->next();
        }

        return $methodBody;
    }

    public function get_use_statements(ReflectionClass $reflection): array
    {
        $contents      = file_get_contents($reflection->getFileName());
        $useStatements = [];

        $namespaces = config('minifier.namespaces', ['App']);

        // Build a regex pattern dynamically based on the namespaces.
        $namespacePattern = implode('|', array_map(fn($ns) => preg_quote($ns, '/'), $namespaces));
        $pattern          = "/^use\s+({$namespacePattern}\\\\[a-zA-Z0-9_\\\\]+)(\s+as\s+([a-zA-Z0-9_]+))?;/m";

        if (preg_match_all(pattern: $pattern, subject: $contents, matches: $matches, flags: PREG_SET_ORDER))
        {
            foreach ($matches as $match)
            {
                $fullClassName             = $match[1];
                $shortName                 = $match[3] ?? class_basename($fullClassName); // Use "class_basename" to get the short name if "as" alias is not used
                $useStatements[$shortName] = $fullClassName;
            }
        }

        return $useStatements;
    }

    private function get_reflection_class($className): ReflectionClass
    {
        try
        {
            $reflection = new ReflectionClass($className);
        } catch (\ReflectionException $e)
        {
            Log::error("Class analysis failed: " . $e->getMessage());
            // Re-throw the exception if you want to ensure that calling code can also handle it
            throw $e;
        }
        return $reflection;
    }

    private function analyze_specific_method(ReflectionClass $reflection, mixed $methodName, $className): array
    {
        if ($reflection->hasMethod($methodName) &&
            $reflection->getMethod($methodName)->getDeclaringClass()->getName() === $className)
        {
            $this->requiredMethods[$reflection->getName()][] = $methodName;
            $methods                                         = [$reflection->getMethod($methodName)];
        } else
        {
            $methods = [];
        }
        return $methods;
    }

    private function analyze_all_methods(ReflectionClass $reflection): array
    {
        $methods = $this->get_own_methods($reflection);
        foreach ($methods as $method)
        {
            $this->requiredMethods[$reflection->getName()][] = $method->getName();
        }

        $parentClass = $reflection->getParentClass();
        if ($parentClass && $this->is_allowed_namespace($parentClass->getNamespaceName()))
        {
            foreach ($this->get_own_methods($parentClass) as $parentMethod)
            {
                if ($parentMethod->isProtected() || $parentMethod->isPublic())
                {
                    $this->requiredMethods[$parentClass->getName()][] = $parentMethod->getName();
                }
            }
        }
        return $methods;
    }
}

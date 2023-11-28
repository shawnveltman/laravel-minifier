<?php

namespace Shawnveltman\LaravelMinifier\CodeParser;

use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use ReflectionMethod;

class ClassContentService
{
    public function createClassFiles(array $requiredClassesAndMethods): void
    {
        $classContents = [];

        foreach ($requiredClassesAndMethods as $className => $methods) {
            $reflection = new ReflectionClass($className);
            $fileContent = file_get_contents($reflection->getFileName());
            $namespace = $reflection->getNamespaceName();
            $useStatements = $this->extractUseStatements($fileContent);
            $shortName = $reflection->getShortName();

            // Construct the class definition with inheritance and interfaces
            $classDefinition = "class {$shortName}";
            if ($parentClass = $reflection->getParentClass()) {
                $classDefinition .= ' extends '.$parentClass->getShortName();
            }
            $interfaces = $reflection->getInterfaceNames();
            if (! empty($interfaces)) {
                $interfaceShortNames = array_map(static function ($interface) {
                    return (new ReflectionClass($interface))->getShortName();
                }, $interfaces);
                $classDefinition .= ' implements '.implode(', ', $interfaceShortNames);
            }

            // Add namespace, use statements, and class definition
            $newClassContent = "<?php\n\nnamespace {$namespace};\n\n{$useStatements}\n\n{$classDefinition}\n{";

            // Add each required method
            foreach ($methods as $methodName) {
                $methodReflection = new ReflectionMethod($className, $methodName);
                $traitReflection = $this->getTraitMethodReflection($methodReflection);

                if ($traitReflection) {
                    $startLine = $traitReflection->getStartLine() - 1;
                    $fileContent = file_get_contents($traitReflection->getDeclaringClass()->getFileName());
                } else {
                    $startLine = $methodReflection->getStartLine() - 1;
                }
                $lines = explode("\n", $fileContent);

                $endLine = $methodReflection->getEndLine();
                $methodContent = implode("\n", array_slice($lines, $startLine, $endLine - $startLine));
                $newClassContent .= "\n\n".$methodContent;
            }

            $newClassContent .= "\n}\n";

            // Ensure that each class is only added once
            if (! isset($classContents[$className])) {
                $classContents[$className] = $newClassContent;
            }
        }

        // Combine all class contents
        $combinedContent = implode("\n\n", $classContents);

        // Save to file
        Storage::disk(config('minifier.disk'))->put(config('minifier.path'), $combinedContent);
    }

    private function getTraitMethodReflection(ReflectionMethod $method): ?ReflectionMethod
    {
        foreach ($method->getDeclaringClass()->getTraits() as $trait) {
            if ($trait->hasMethod($method->getName())) {
                return $trait->getMethod($method->getName());
            }
        }

        return null;
    }

    private function extractUseStatements($fileContent)
    {
        $useStatements = [];

        // Split the file content into lines
        $lines = explode("\n", $fileContent);

        // Iterate through the lines and extract use statements
        foreach ($lines as $line) {
            // Check if the line starts with 'use'
            if (preg_match('/^\s*use\s+[a-zA-Z0-9_\\\\]+;/', $line)) {
                $useStatements[] = trim($line);
            } elseif (! empty(trim($line)) && ! str_starts_with(trim($line), 'use')) {
                // Stop parsing when we reach a line that is neither a 'use' statement nor empty
                continue;
            }
        }

        return implode("\n", $useStatements);
    }
}

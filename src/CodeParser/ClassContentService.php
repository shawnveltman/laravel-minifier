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
            $lines = explode("\n", $fileContent);

            // Manually reconstruct the class definition
            $namespace = $reflection->getNamespaceName();
            $useStatements = $this->extractUseStatements($fileContent);
            $classDefinition = "class {$reflection->getShortName()}";

            // Add namespace and use statements
            $newClassContent = "<?php\n\nnamespace {$namespace};\n\n{$useStatements}\n\n{$classDefinition}\n{";

            // Add each required method
            foreach ($methods as $methodName) {
                $methodReflection = new ReflectionMethod($className, $methodName);
                $startLine = $methodReflection->getStartLine() - 1;
                $endLine = $methodReflection->getEndLine();
                $methodContent = implode("\n", array_slice($lines, $startLine, $endLine - $startLine));
                $newClassContent .= "\n\n".$methodContent;
            }

            $newClassContent .= "\n}\n";

            $classContents[] = $newClassContent;
        }

        // Combine all class contents
        $combinedContent = implode("\n\n", $classContents);

        // Save to file
        Storage::disk(config('minifier.disk'))->put(config('minifier.path'), $combinedContent);
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
                break;
            }
        }

        return implode("\n", $useStatements);
    }
}

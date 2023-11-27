<?php

namespace Shawnveltman\LaravelMinifier\Tests\Feature;

use Exception;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;
use Shawnveltman\LaravelMinifier\CodeParser\ClassContentService;
use Shawnveltman\LaravelMinifier\CodeParser\MethodAnalysisService;
use Shawnveltman\LaravelMinifier\Tests\Fixtures\
{AbstractClassWithMethods,
    AliasesUseStatementClass,
    BaseClass,
    ChildClass,
    ClassUsingInheritedTraits,
    ClassWithMultipleNamespaceAliases,
    ClassWithTrait,
    DirectInstantiationClass,
    EmptyClass,
    InterfaceImplementingClass,
    InterfaceToImplement,
    MultipleMethodsClass,
    OtherClass,
    StaticMethodClass,
    UseStatementClass};


it('analyzes a class and returns all own method dependencies', function () {
    Config::set('minifier.namespaces', [
        'App',
        'Shawnveltman\LaravelMinifier\Tests\Fixtures',
    ]);
    $analysisService = new MethodAnalysisService();

    // Analyze the BaseClass which has no dependencies.
    $values = $analysisService->analyze_class(BaseClass::class);
    expect($values)->toBeArray();

    // Now analyze the ChildClass which should have dependencies based on the parentMethod and parentProtectedMethod.
    $values = $analysisService->analyze_class(ChildClass::class);

    expect($values)->toBeArray();
    // We should see BaseClass's methods in the returned array.
    expect($values)->toHaveKey(BaseClass::class);
    expect($values[BaseClass::class])->toContain('parentMethod');
    expect($values[BaseClass::class])->toContain('parentProtectedMethod');
});

it('creates class files with the required methods', function () {
    Config::set('minifier.namespaces', [
        'App',
        'Shawnveltman\LaravelMinifier\Tests\Fixtures',
    ]);
    $contentService  = new ClassContentService();
    $analysisService = new MethodAnalysisService();

    // Mock Storage Facade to prevent actual filesystem interaction
    Storage::fake('local');

    // Use MethodAnalysisService to get the correct set of classes and methods
    $requiredClassesAndMethods = $analysisService->analyze_class(ChildClass::class);

    Config::set('minifier.disk', 'local');
    Config::set('minifier.path', 'output.php');

    $contentService->createClassFiles($requiredClassesAndMethods);

    // Assert that the file was created
    Storage::disk('local')->assertExists('output.php');

    // Obtain the content of the stored file
    $content = Storage::disk('local')->get('output.php');

    // Assertions based on the expected content of the file.
    // Check if the ChildClass and the methods it uses from the BaseClass are generated in the output.

    expect($content)->toContain('class ChildClass');
    expect($content)->toContain('function childMethod()');

    expect($content)->toContain('class BaseClass');
    expect($content)->toContain('function parentMethod()');
    expect($content)->toContain('function parentProtectedMethod()');

    // Test if the ClassContentService successfully removes the methods not listed in the $requiredClassesAndMethods
    // Assuming that BaseClass has other methods that aren't used and shouldn't be in the output
    expect($content)->not->toContain('function someOtherMethod()');
});

it('analyzes a class with multiple methods and tracks their dependencies', function () {
    $analysisService = new MethodAnalysisService();

    // Analyze MultipleMethodsClass which has internal method dependencies.
    $values = $analysisService->analyze_class(MultipleMethodsClass::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey(MultipleMethodsClass::class);
    expect($values[MultipleMethodsClass::class])->toContain('firstMethod');
    expect($values[MultipleMethodsClass::class])->toContain('secondMethod');
});

it('analyzes a class using a trait and includes the trait methods', function () {
    $analysisService = new MethodAnalysisService();

    // Analyze ClassWithTrait which uses a trait with its own methods.
    $values = $analysisService->analyze_class(ClassWithTrait::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey("Shawnveltman\LaravelMinifier\Tests\Fixtures\ExampleTrait");
//    expect($values["Shawnveltman\LaravelMinifier\Tests\Fixtures\ClassWithTrait"])->toContain('methodUsingTrait');
    expect($values["Shawnveltman\LaravelMinifier\Tests\Fixtures\ExampleTrait"])->toContain('traitMethod');
});

it('analyzes a class using a trait and includes the trait methods and methods of the class itself', function () {
    $analysisService = new MethodAnalysisService();

    // Analyze ClassWithTrait which uses a trait with its own methods.
    $values = $analysisService->analyze_class(ClassWithTrait::class);

    // Validate that the array of dependencies was returned.
    expect($values)->toBeArray();

    // Ensure the trait is included as a dependency.
    expect($values)->toHaveKey("Shawnveltman\LaravelMinifier\Tests\Fixtures\ExampleTrait");

    // Ensure that the method from the trait is included.
    expect($values["Shawnveltman\LaravelMinifier\Tests\Fixtures\ExampleTrait"])->toContain('traitMethod');
    // Ensure that the class being analyzed is included with its own methods.
    expect($values)->toHaveKey("Shawnveltman\LaravelMinifier\Tests\Fixtures\ClassWithTrait");
    expect($values["Shawnveltman\LaravelMinifier\Tests\Fixtures\ClassWithTrait"])->toContain('methodUsingTrait');
});

it('throws an exception when a nonexistent class is analyzed', function () {
    $analysisService = new MethodAnalysisService();
    $nonExistentClass = 'NonExistentClass';
    $analysisService->analyze_class($nonExistentClass);
})->throws(Exception::class);

it('throws an exception when a non-class is passed', function () {
    $analysisService = new MethodAnalysisService();
    $nonClass = 'SomeRandomString';
    $analysisService->analyze_class($nonClass);
})->throws(Exception::class);

it('analyzes abstract classes and abstract methods correctly', function () {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(AbstractClassWithMethods::class);
    expect($values)->toBeArray();
    expect($values[AbstractClassWithMethods::class])->toContain('abstractMethod');
});

it('handles use statements with aliases correctly', function () {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(AliasesUseStatementClass::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey(AliasesUseStatementClass::class);
    expect($values[AliasesUseStatementClass::class])->toContain('methodWithAliasUse');
});

it('tracks classes instantiated within methods', function() {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(DirectInstantiationClass::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey(DirectInstantiationClass::class);
    expect($values[DirectInstantiationClass::class])->toContain('methodWithInstantiation');
    expect($values)->toHaveKey(OtherClass::class);
    expect($values[OtherClass::class])->toContain('doSomething');
});

it('includes protected parent methods accessed by child classes', function () {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(ChildClass::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey(BaseClass::class);
    expect($values[BaseClass::class])->toContain('parentProtectedMethod');
});

it('includes depended-upon static methods', function() {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(StaticMethodClass::class);
    expect($values)->toBeArray();
    expect($values)->toHaveKey(StaticMethodClass::class);
    expect($values[StaticMethodClass::class])->toContain('staticMethod');
});

it('handles empty classes or edge cases appropriately', function () {
    $analysisService = new MethodAnalysisService();
    $values = $analysisService->analyze_class(EmptyClass::class);
    expect($values)->toBeArray();
    expect($values)->toEqual([]);
});

it('resolves trait inheritance dependencies correctly', function () {
    Config::set('minifier.namespaces', [
        'App',
        'Shawnveltman\LaravelMinifier\Tests\Fixtures',
    ]);
    $analysisService = new MethodAnalysisService();

    $values = $analysisService->analyze_class(ClassUsingInheritedTraits::class);

    expect($values)->toBeArray();
    expect($values)->toHaveKey(ClassUsingInheritedTraits::class);
    expect($values[ClassUsingInheritedTraits::class])->toContain('inheritedTraitMethod');
})->skip(fn () => !class_exists(ClassUsingInheritedTraits::class));

it('analyzes classes implementing interfaces correctly', function () {
    $analysisService = new MethodAnalysisService();

    // Assuming `InterfaceImplementingClass` implements `InterfaceToImplement`
    $values = $analysisService->analyze_class(InterfaceImplementingClass::class);

    expect($values)->toBeArray();
    expect($values)->toHaveKey(InterfaceImplementingClass::class);

    // Use reflection to get the methods from the interface
    $interfaceReflection = new ReflectionClass(InterfaceToImplement::class);
    $methods = $interfaceReflection->getMethods(ReflectionMethod::IS_PUBLIC);

    foreach ($methods as $method) {
        expect($values[InterfaceImplementingClass::class])->toContain($method->getName());
    }

})->skip(fn () => !interface_exists(InterfaceToImplement::class) || !class_exists(InterfaceImplementingClass::class));
it('manages multiple namespace aliases correctly', function () {
    $analysisService = new MethodAnalysisService();

    $values = $analysisService->analyze_class(ClassWithMultipleNamespaceAliases::class);

    expect($values)->toBeArray();
    expect($values[ClassWithMultipleNamespaceAliases::class])->toBe(['methodWithMultipleAliases']);
})->skip(fn () => !class_exists(ClassWithMultipleNamespaceAliases::class));

it('handles errors and logs them appropriately when a class file cannot be analyzed', function () {
    $analysisService = new MethodAnalysisService();
    $brokenClassName = 'NonExistentClass';

    Log::shouldReceive('error')
        ->once()
        ->withArgs(fn($message) => str_contains($message, $brokenClassName));

    $analysisService->analyze_class($brokenClassName);
})->throws(ReflectionException::class);

it('ensures the initial class is not added twice', function () {
    // Set up the allowed namespaces configuration
    Config::set('minifier.namespaces', [
        'App',
        'Shawnveltman\LaravelMinifier\Tests\Fixtures',
    ]);

    // Instantiate the MethodAnalysisService
    $analysisService = new MethodAnalysisService();

    // Analyze a class that is known to have dependencies
    // Replace 'YourInitialClass' with the actual class you want to test
    $values = $analysisService->analyze_class(UseStatementClass::class);

    // Check that the array has keys for each class
    expect($values)->toBeArray();

    // Check that the initial class is listed only once
    $initialClassOccurrences = array_reduce($values, function ($carry, $methods) use ($values) {
        $className = array_search($methods, $values);
        return $carry + ($className === UseStatementClass::class ? 1 : 0);
    }, 0);

    expect($initialClassOccurrences)->toEqual(1);
});



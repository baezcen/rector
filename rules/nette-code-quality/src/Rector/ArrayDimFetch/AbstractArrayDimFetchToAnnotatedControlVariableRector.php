<?php

declare(strict_types=1);

namespace Rector\NetteCodeQuality\Rector\ArrayDimFetch;

use PhpParser\Node\Expr\ArrayDimFetch;
use PhpParser\Node\Expr\Assign;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Expression;
use PHPStan\Type\ObjectType;
use Rector\Core\Rector\AbstractRector;
use Rector\NetteCodeQuality\DocBlock\VarAnnotationManipulator;
use Rector\NetteCodeQuality\Naming\NetteControlNaming;
use Rector\NetteCodeQuality\NodeAnalyzer\ControlDimFetchAnalyzer;
use Rector\NodeTypeResolver\Node\AttributeKey;

/**
 * @sponsor Thanks https://amateri.com for sponsoring this rule - visit them on https://www.startupjobs.cz/startup/scrumworks-s-r-o
 */
abstract class AbstractArrayDimFetchToAnnotatedControlVariableRector extends AbstractRector
{
    /**
     * @var ControlDimFetchAnalyzer
     */
    protected $controlDimFetchAnalyzer;

    /**
     * @var NetteControlNaming
     */
    protected $netteControlNaming;

    /**
     * @var VarAnnotationManipulator
     */
    protected $varAnnotationManipulator;

    /**
     * @var string[]
     */
    private $alreadyInitializedAssignsClassMethodObjectHashes = [];

    /**
     * @required
     */
    public function autowireAbstractArrayDimFetchToAnnotatedControlVariableRector(
        VarAnnotationManipulator $varAnnotationManipulator,
        ControlDimFetchAnalyzer $controlDimFetchAnalyzer,
        NetteControlNaming $netteControlNaming
    ): void {
        $this->controlDimFetchAnalyzer = $controlDimFetchAnalyzer;
        $this->netteControlNaming = $netteControlNaming;
        $this->varAnnotationManipulator = $varAnnotationManipulator;
    }

    /**
     * @return string[]
     */
    public function getNodeTypes(): array
    {
        return [ArrayDimFetch::class];
    }

    protected function addAssignExpressionForFirstCase(
        string $variableName,
        ArrayDimFetch $arrayDimFetch,
        ObjectType $controlObjectType
    ): void {
        if ($this->shouldSkipForAlreadyAddedInCurrentClassMethod($arrayDimFetch, $variableName)) {
            return;
        }

        $assignExpression = $this->createAssignExpression($variableName, $arrayDimFetch);

        $this->varAnnotationManipulator->decorateNodeWithInlineVarType(
            $assignExpression,
            $controlObjectType,
            $variableName
        );

        $currentStatement = $arrayDimFetch->getAttribute(AttributeKey::CURRENT_STATEMENT);
        $this->addNodeBeforeNode($assignExpression, $currentStatement);
    }

    protected function isBeingAssignedOrInitialized(ArrayDimFetch $arrayDimFetch): bool
    {
        $parent = $arrayDimFetch->getAttribute(AttributeKey::PARENT_NODE);
        if (! $parent instanceof Assign) {
            return false;
        }

        if ($parent->var === $arrayDimFetch) {
            return true;
        }

        return $parent->expr === $arrayDimFetch;
    }

    private function createAssignExpression(string $variableName, ArrayDimFetch $arrayDimFetch): Expression
    {
        $variable = new Variable($variableName);
        $assignedArrayDimFetch = clone $arrayDimFetch;
        $assign = new Assign($variable, $assignedArrayDimFetch);

        return new Expression($assign);
    }

    private function shouldSkipForAlreadyAddedInCurrentClassMethod(
        ArrayDimFetch $arrayDimFetch,
        string $variableName
    ): bool {
        /** @var ClassMethod|null $classMethod */
        $classMethod = $arrayDimFetch->getAttribute(AttributeKey::METHOD_NODE);

        if ($classMethod === null) {
            return false;
        }

        $classMethodObjectHash = spl_object_hash($classMethod) . $variableName;
        if (in_array($classMethodObjectHash, $this->alreadyInitializedAssignsClassMethodObjectHashes, true)) {
            return true;
        }

        $this->alreadyInitializedAssignsClassMethodObjectHashes[] = $classMethodObjectHash;

        return false;
    }
}

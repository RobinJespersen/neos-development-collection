<?php
namespace Neos\Neos\Tests\Functional\Service;

/*
 * This file is part of the Neos.Neos package.
 *
 * (c) Contributors of the Neos Project - www.neos.io
 *
 * This package is Open Source Software. For the full copyright and license
 * information, please view the LICENSE file which was distributed with this
 * source code.
 */

use Neos\Flow\Tests\FunctionalTestCase;
use Neos\Neos\Service\NodeTypeSchemaBuilder;

/**
 * Testcase for the NodeTypeSchemaBuilder
 */
class NodeTypeSchemaBuilderTest extends FunctionalTestCase
{
    /**
     * @var NodeTypeSchemaBuilder
     */
    protected $nodeTypeSchemaBuilder;

    /**
     * The test schema
     *
     * @var array
     */
    protected $schema;

    public function setUp(): void
    {
        parent::setUp();
        $this->nodeTypeSchemaBuilder = $this->objectManager->get(NodeTypeSchemaBuilder::class);
        $this->schema = $this->nodeTypeSchemaBuilder->generateNodeTypeSchema();
    }

    /**
     * @test
     */
    public function inheritanceMapContainsTransitiveSubTypes()
    {
        self::assertTrue(array_key_exists('Neos.Neos.BackendSchemaControllerTest:Document', $this->schema['inheritanceMap']['subTypes']), 'Document must be found in InheritanceMap');
        $expectedSubTypesOfDocument = [
            'Neos.Neos.BackendSchemaControllerTest:Page',
            'Neos.Neos.BackendSchemaControllerTest:SubPage',
            'Neos.Neos.BackendSchemaControllerTest:Folder'
        ];

        self::assertEquals($expectedSubTypesOfDocument, $this->schema['inheritanceMap']['subTypes']['Neos.Neos.BackendSchemaControllerTest:Document']);
    }

    /**
     * @test
     */
    public function nodeTypesContainCorrectSuperTypes()
    {
        self::assertTrue(array_key_exists('Neos.Neos.BackendSchemaControllerTest:AlohaNodeType', $this->schema['nodeTypes']), 'AlohaNodeType');

        $expectedSuperTypes = ['Neos.Neos.BackendSchemaControllerTest:ParentAlohaNodeType' => true];
        $expectedPropertyConfiguration = [
            'alignment' => ['left', 'center', 'right'],
            'format' => ['h3', 'sup']
        ];

        self::assertEquals($expectedSuperTypes, $this->schema['nodeTypes']['Neos.Neos.BackendSchemaControllerTest:AlohaNodeType']['superTypes']);
        self::assertEquals($expectedPropertyConfiguration, $this->schema['nodeTypes']['Neos.Neos.BackendSchemaControllerTest:AlohaNodeType']['properties']['text']['ui']['aloha']);
    }

    /**
     * @test
     */
    public function theNodeTypeSchemaIncludesSubTypesInheritanceMap()
    {
        $subTypesDefinition = $this->schema['inheritanceMap']['subTypes'];

        self::assertContains('Neos.Neos.BackendSchemaControllerTest:Document', $subTypesDefinition['Neos.Neos.BackendSchemaControllerTest:Node']);
        self::assertContains('Neos.Neos.BackendSchemaControllerTest:Content', $subTypesDefinition['Neos.Neos.BackendSchemaControllerTest:Node']);
        self::assertContains('Neos.Neos.BackendSchemaControllerTest:Page', $subTypesDefinition['Neos.Neos.BackendSchemaControllerTest:Node']);
        self::assertContains('Neos.Neos.BackendSchemaControllerTest:SubPage', $subTypesDefinition['Neos.Neos.BackendSchemaControllerTest:Node']);
        self::assertContains('Neos.Neos.BackendSchemaControllerTest:Text', $subTypesDefinition['Neos.Neos.BackendSchemaControllerTest:Node']);
    }

    /**
     * @test
     */
    public function alohaUiConfigurationPartsAreActualArrayAndDontContainExcludedElements()
    {
        $alohaConfiguration = $this->schema['nodeTypes']['Neos.Neos.BackendSchemaControllerTest:AlohaNodeType']['properties']['text']['ui']['aloha'];
        self::assertIsArray($alohaConfiguration['alignment']);
        self::assertIsArray($alohaConfiguration['format']);

        self::assertArrayNotHasKey('h3', $alohaConfiguration['format']);
        self::assertArrayNotHasKey('sup', $alohaConfiguration['format']);

        self::assertEquals(['left', 'center', 'right'], $alohaConfiguration['alignment']);
        self::assertEquals(['h3', 'sup'], $alohaConfiguration['format']);
    }

    /**
     * @test
     */
    public function constraintsAreEvaluatedForANodeType()
    {
        $expectedConstraints = [
            'nodeTypes' => [
                'Neos.Neos.BackendSchemaControllerTest:SubPage' => true
            ],
            'childNodes' => []
        ];
        self::assertEquals($expectedConstraints, $this->schema['constraints']['Neos.Neos.BackendSchemaControllerTest:Page']);
    }

    /**
     * @test
     */
    public function constraintsForNamedChildNodeTypesAreEvaluatedForANodeType()
    {
        self::assertFalse(array_key_exists('Neos.Neos.BackendSchemaControllerTest:SubPage', $this->schema['constraints']['Neos.Neos.BackendSchemaControllerTest:TwoColumn']['childNodes']['column1']['nodeTypes']));
        self::assertContains('Neos.Neos.BackendSchemaControllerTest:AlohaNodeType', $this->schema['constraints']['Neos.Neos.BackendSchemaControllerTest:TwoColumn']['childNodes']['column1']['nodeTypes']);
    }
}

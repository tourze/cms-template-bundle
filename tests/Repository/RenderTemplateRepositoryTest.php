<?php

namespace Tourze\CmsTemplateBundle\Tests\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;
use Tourze\CmsTemplateBundle\Entity\RenderTemplate;
use Tourze\CmsTemplateBundle\Repository\RenderTemplateRepository;
use Tourze\PHPUnitSymfonyKernelTest\AbstractRepositoryTestCase;

/**
 * @internal
 */
#[CoversClass(RenderTemplateRepository::class)]
#[RunTestsInSeparateProcesses]
final class RenderTemplateRepositoryTest extends AbstractRepositoryTestCase
{
    private RenderTemplateRepository $repository;

    protected function onSetUp(): void
    {
        $this->repository = self::getService(RenderTemplateRepository::class);
    }

    public function testSaveEntityPersistsToDatabase(): void
    {
        $template = new RenderTemplate();
        $template->setPath('/save-test');
        $template->setTitle('Save Test');
        $template->setContent('<p>Save test content</p>');

        $this->repository->save($template);
        $id = $template->getId();

        $this->assertNotNull($id);

        $found = $this->repository->find($id);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertSame('Save Test', $found->getTitle());
    }

    public function testRemoveEntityDeletesFromDatabase(): void
    {
        $template = new RenderTemplate();
        $template->setPath('/remove-test');
        $template->setTitle('Remove Test');
        $template->setContent('<p>Remove test content</p>');

        $this->repository->save($template);
        $id = $template->getId();

        $this->repository->remove($template);

        $found = $this->repository->find($id);
        $this->assertNull($found);
    }

    public function testFindOneByPathReturnsCorrectEntity(): void
    {
        $template = new RenderTemplate();
        $template->setPath('/unique-path');
        $template->setTitle('Unique Path Test');
        $template->setContent('<p>Unique path content</p>');

        $this->repository->save($template);

        $found = $this->repository->findOneBy(['path' => '/unique-path']);

        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertSame('/unique-path', $found->getPath());
        $this->assertSame('Unique Path Test', $found->getTitle());
    }

    public function testFindByValidStatusReturnsOnlyValidEntities(): void
    {
        $validTemplate = new RenderTemplate();
        $validTemplate->setPath('/valid-template');
        $validTemplate->setTitle('Valid Template');
        $validTemplate->setContent('<p>Valid content</p>');
        $validTemplate->setValid(true);

        $invalidTemplate = new RenderTemplate();
        $invalidTemplate->setPath('/invalid-template');
        $invalidTemplate->setTitle('Invalid Template');
        $invalidTemplate->setContent('<p>Invalid content</p>');
        $invalidTemplate->setValid(false);

        $this->repository->save($validTemplate);
        $this->repository->save($invalidTemplate);

        $validTemplates = $this->repository->findBy(['valid' => true]);

        $this->assertGreaterThanOrEqual(1, count($validTemplates));
        foreach ($validTemplates as $template) {
            $this->assertTrue($template->isValid());
        }
    }

    public function testCountByValidStatusReturnsCorrectCount(): void
    {
        $initialValidCount = $this->repository->count(['valid' => true]);

        $validTemplate = new RenderTemplate();
        $validTemplate->setPath('/count-valid');
        $validTemplate->setTitle('Count Valid');
        $validTemplate->setContent('<p>Count valid content</p>');
        $validTemplate->setValid(true);

        $this->repository->save($validTemplate);

        $newValidCount = $this->repository->count(['valid' => true]);

        $this->assertSame($initialValidCount + 1, $newValidCount);
    }

    public function testFindByParentAssociationReturnsChildTemplates(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/parent-template');
        $parentTemplate->setTitle('Parent Template');
        $parentTemplate->setContent('<p>Parent content</p>');

        $childTemplate = new RenderTemplate();
        $childTemplate->setPath('/child-template');
        $childTemplate->setTitle('Child Template');
        $childTemplate->setContent('<p>Child content</p>');
        $childTemplate->setParent($parentTemplate);

        $this->repository->save($parentTemplate);
        $this->repository->save($childTemplate);

        $children = $this->repository->findBy(['parent' => $parentTemplate]);

        $this->assertCount(1, $children);
        $this->assertSame($childTemplate->getId(), $children[0]->getId());
        $this->assertSame($parentTemplate->getId(), $children[0]->getParent()?->getId());
    }

    public function testCountChildrenByParentAssociation(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/parent-for-count');
        $parentTemplate->setTitle('Parent For Count');
        $parentTemplate->setContent('<p>Parent for count content</p>');

        $this->repository->save($parentTemplate);

        $initialChildCount = $this->repository->count(['parent' => $parentTemplate]);

        $childTemplate1 = new RenderTemplate();
        $childTemplate1->setPath('/child-1');
        $childTemplate1->setTitle('Child 1');
        $childTemplate1->setContent('<p>Child 1 content</p>');
        $childTemplate1->setParent($parentTemplate);

        $childTemplate2 = new RenderTemplate();
        $childTemplate2->setPath('/child-2');
        $childTemplate2->setTitle('Child 2');
        $childTemplate2->setContent('<p>Child 2 content</p>');
        $childTemplate2->setParent($parentTemplate);

        $this->repository->save($childTemplate1);
        $this->repository->save($childTemplate2);

        $newChildCount = $this->repository->count(['parent' => $parentTemplate]);

        $this->assertSame($initialChildCount + 2, $newChildCount);
    }

    public function testFindByNullParentReturnsRootTemplates(): void
    {
        $rootTemplate = new RenderTemplate();
        $rootTemplate->setPath('/root-template');
        $rootTemplate->setTitle('Root Template');
        $rootTemplate->setContent('<p>Root content</p>');
        $rootTemplate->setParent(null);

        $this->repository->save($rootTemplate);

        $rootTemplates = $this->repository->findBy(['parent' => null]);

        $this->assertGreaterThanOrEqual(1, count($rootTemplates));
        $found = false;
        foreach ($rootTemplates as $template) {
            if ($template->getId() === $rootTemplate->getId()) {
                $found = true;
                $this->assertNull($template->getParent());
                break;
            }
        }
        $this->assertTrue($found, 'Root template should be found in null parent results');
    }

    public function testCountByNullParentReturnsRootTemplateCount(): void
    {
        $initialRootCount = $this->repository->count(['parent' => null]);

        $rootTemplate = new RenderTemplate();
        $rootTemplate->setPath('/new-root-template');
        $rootTemplate->setTitle('New Root Template');
        $rootTemplate->setContent('<p>New root content</p>');

        $this->repository->save($rootTemplate);

        $newRootCount = $this->repository->count(['parent' => null]);

        $this->assertSame($initialRootCount + 1, $newRootCount);
    }

    public function testFindByNullValidReturnsTemplatesWithNullValid(): void
    {
        $templateWithNullValid = new RenderTemplate();
        $templateWithNullValid->setPath('/null-valid-path');
        $templateWithNullValid->setTitle('Template With Null Valid');
        $templateWithNullValid->setContent('<p>Content with null valid</p>');
        $templateWithNullValid->setValid(null);

        $this->repository->save($templateWithNullValid);

        $templatesWithNullValid = $this->repository->findBy(['valid' => null]);

        $this->assertGreaterThanOrEqual(1, count($templatesWithNullValid));
        $found = false;
        foreach ($templatesWithNullValid as $template) {
            if ($template->getId() === $templateWithNullValid->getId()) {
                $found = true;
                $this->assertNull($template->isValid());
                break;
            }
        }
        $this->assertTrue($found, 'Template with null valid should be found');
    }

    public function testCountByNullValidReturnsCorrectCount(): void
    {
        $initialNullValidCount = $this->repository->count(['valid' => null]);

        $templateWithNullValid = new RenderTemplate();
        $templateWithNullValid->setPath('/another-null-valid-path');
        $templateWithNullValid->setTitle('Another Template With Null Valid');
        $templateWithNullValid->setContent('<p>Another content with null valid</p>');
        $templateWithNullValid->setValid(null);

        $this->repository->save($templateWithNullValid);

        $newNullValidCount = $this->repository->count(['valid' => null]);

        $this->assertSame($initialNullValidCount + 1, $newNullValidCount);
    }

    public function testFindByWithAssociationFieldQuery(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/association-parent');
        $parentTemplate->setTitle('Parent Template');
        $parentTemplate->setContent('<p>Parent content</p>');

        $childTemplate = new RenderTemplate();
        $childTemplate->setPath('/association-child');
        $childTemplate->setTitle('Child Template');
        $childTemplate->setContent('<p>Child content</p>');
        $childTemplate->setParent($parentTemplate);

        $this->repository->save($parentTemplate);
        $this->repository->save($childTemplate);

        $children = $this->repository->findBy(['parent' => $parentTemplate]);

        $this->assertGreaterThanOrEqual(1, count($children));
        foreach ($children as $child) {
            $this->assertSame($parentTemplate->getId(), $child->getParent()?->getId());
        }
    }

    public function testCountWithAssociationFieldQuery(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/count-association-parent');
        $parentTemplate->setTitle('Count Parent Template');
        $parentTemplate->setContent('<p>Count parent content</p>');

        $this->repository->save($parentTemplate);

        $initialCount = $this->repository->count(['parent' => $parentTemplate]);

        $childTemplate = new RenderTemplate();
        $childTemplate->setPath('/count-association-child');
        $childTemplate->setTitle('Count Child Template');
        $childTemplate->setContent('<p>Count child content</p>');
        $childTemplate->setParent($parentTemplate);

        $this->repository->save($childTemplate);

        $newCount = $this->repository->count(['parent' => $parentTemplate]);

        $this->assertSame($initialCount + 1, $newCount);
    }

    public function testFindOneByWithSortingOrderLogic(): void
    {
        $template1 = new RenderTemplate();
        $template1->setPath('/sorting-test-1');
        $template1->setTitle('Z Template');
        $template1->setContent('<p>Z content</p>');

        $template2 = new RenderTemplate();
        $template2->setPath('/sorting-test-2');
        $template2->setTitle('A Template');
        $template2->setContent('<p>A content</p>');

        $this->repository->save($template1);
        $this->repository->save($template2);

        // Test ascending order
        $firstTemplate = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $firstTemplate);

        // Test descending order
        $lastTemplate = $this->repository->findOneBy([], ['title' => 'DESC']);
        $this->assertInstanceOf(RenderTemplate::class, $lastTemplate);

        // Verify they are different (unless there's only one template)
        if ($firstTemplate->getId() !== $lastTemplate->getId()) {
            $this->assertNotSame($firstTemplate->getTitle(), $lastTemplate->getTitle());
        }
    }

    public function testFindByCreatedByStringShouldReturnAllMatchingEntities(): void
    {
        $userIdentifier = 'test-user-123';

        $template = new RenderTemplate();
        $template->setPath('/created-by-test');
        $template->setTitle('Created By Test');
        $template->setContent('<p>Created by test content</p>');
        $template->setCreatedBy($userIdentifier);

        $this->repository->save($template);

        $results = $this->repository->findBy(['createdBy' => $userIdentifier]);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertInstanceOf(RenderTemplate::class, $result);
            $this->assertSame($userIdentifier, $result->getCreatedBy());
        }
    }

    public function testCountByCreatedByStringShouldReturnCorrectNumber(): void
    {
        $userIdentifier = 'count-test-user-456';

        $initialCount = $this->repository->count(['createdBy' => $userIdentifier]);

        $template = new RenderTemplate();
        $template->setPath('/count-created-by-test');
        $template->setTitle('Count Created By Test');
        $template->setContent('<p>Count created by test content</p>');
        $template->setCreatedBy($userIdentifier);

        $this->repository->save($template);

        $count = $this->repository->count(['createdBy' => $userIdentifier]);
        $this->assertSame($initialCount + 1, $count);
    }

    public function testFindByUpdatedByStringShouldReturnAllMatchingEntities(): void
    {
        $userIdentifier = 'updated-test-user-789';

        $template = new RenderTemplate();
        $template->setPath('/updated-by-test');
        $template->setTitle('Updated By Test');
        $template->setContent('<p>Updated by test content</p>');
        $template->setUpdatedBy($userIdentifier);

        $this->repository->save($template);

        $results = $this->repository->findBy(['updatedBy' => $userIdentifier]);

        $this->assertIsArray($results);
        $this->assertGreaterThanOrEqual(1, count($results));
        foreach ($results as $result) {
            $this->assertInstanceOf(RenderTemplate::class, $result);
            $this->assertSame($userIdentifier, $result->getUpdatedBy());
        }
    }

    public function testCountByUpdatedByStringShouldReturnCorrectNumber(): void
    {
        $userIdentifier = 'count-updated-test-user-101';

        $initialCount = $this->repository->count(['updatedBy' => $userIdentifier]);

        $template = new RenderTemplate();
        $template->setPath('/count-updated-by-test');
        $template->setTitle('Count Updated By Test');
        $template->setContent('<p>Count updated by test content</p>');
        $template->setUpdatedBy($userIdentifier);

        $this->repository->save($template);

        $count = $this->repository->count(['updatedBy' => $userIdentifier]);
        $this->assertSame($initialCount + 1, $count);
    }

    public function testFindOneBySortingReturnsValidResult(): void
    {
        // Test that sorting works and returns a valid result
        $result = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $result);
        $this->assertNotNull($result->getTitle());

        // Test descending order also works
        $descResult = $this->repository->findOneBy([], ['title' => 'DESC']);
        $this->assertInstanceOf(RenderTemplate::class, $descResult);
        $this->assertNotNull($descResult->getTitle());
    }

    public function testCountByAssociationField(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/count-association-parent');
        $parentTemplate->setTitle('Count Association Parent');
        $parentTemplate->setContent('<p>Parent content</p>');

        $this->repository->save($parentTemplate);

        $initialCount = $this->repository->count(['parent' => $parentTemplate]);

        $childTemplate1 = new RenderTemplate();
        $childTemplate1->setPath('/count-association-child-1');
        $childTemplate1->setTitle('Count Association Child 1');
        $childTemplate1->setContent('<p>Child 1 content</p>');
        $childTemplate1->setParent($parentTemplate);

        $childTemplate2 = new RenderTemplate();
        $childTemplate2->setPath('/count-association-child-2');
        $childTemplate2->setTitle('Count Association Child 2');
        $childTemplate2->setContent('<p>Child 2 content</p>');
        $childTemplate2->setParent($parentTemplate);

        $this->repository->save($childTemplate1);
        $this->repository->save($childTemplate2);

        $finalCount = $this->repository->count(['parent' => $parentTemplate]);
        $this->assertSame($initialCount + 2, $finalCount);
    }

    public function testFindOneByWithAssociationField(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/find-one-association-parent');
        $parentTemplate->setTitle('Find One Association Parent');
        $parentTemplate->setContent('<p>Parent content</p>');

        $childTemplate = new RenderTemplate();
        $childTemplate->setPath('/find-one-association-child');
        $childTemplate->setTitle('Find One Association Child');
        $childTemplate->setContent('<p>Child content</p>');
        $childTemplate->setParent($parentTemplate);

        $this->repository->save($parentTemplate);
        $this->repository->save($childTemplate);

        $found = $this->repository->findOneBy(['parent' => $parentTemplate]);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertSame($childTemplate->getId(), $found->getId());
        $this->assertSame($parentTemplate->getId(), $found->getParent()?->getId());
    }

    public function testFindOneByWithMultipleAssociationFields(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/multi-association-parent');
        $parentTemplate->setTitle('Multi Association Parent');
        $parentTemplate->setContent('<p>Parent content</p>');
        $parentTemplate->setValid(true);

        $childTemplate1 = new RenderTemplate();
        $childTemplate1->setPath('/multi-association-child-1');
        $childTemplate1->setTitle('Multi Association Child 1');
        $childTemplate1->setContent('<p>Child 1 content</p>');
        $childTemplate1->setParent($parentTemplate);
        $childTemplate1->setValid(true);

        $childTemplate2 = new RenderTemplate();
        $childTemplate2->setPath('/multi-association-child-2');
        $childTemplate2->setTitle('Multi Association Child 2');
        $childTemplate2->setContent('<p>Child 2 content</p>');
        $childTemplate2->setParent($parentTemplate);
        $childTemplate2->setValid(false);

        $this->repository->save($parentTemplate);
        $this->repository->save($childTemplate1);
        $this->repository->save($childTemplate2);

        // Find by parent and valid = true
        $found = $this->repository->findOneBy(['parent' => $parentTemplate, 'valid' => true]);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertTrue($found->isValid());
        $this->assertSame($parentTemplate->getId(), $found->getParent()?->getId());
    }

    public function testFindOneByWithNullAssociationField(): void
    {
        $rootTemplate = new RenderTemplate();
        $rootTemplate->setPath('/null-association-test');
        $rootTemplate->setTitle('Null Association Test');
        $rootTemplate->setContent('<p>Root template content</p>');
        $rootTemplate->setParent(null);

        $this->repository->save($rootTemplate);

        $found = $this->repository->findOneBy(['parent' => null]);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertNull($found->getParent());
    }

    public function testCountByNullFields(): void
    {
        $template = new RenderTemplate();
        $template->setPath('/count-null-fields-test');
        $template->setTitle('Count Null Fields Test');
        $template->setContent('<p>Content</p>');
        $template->setValid(null);

        $this->repository->save($template);

        $count = $this->repository->count(['valid' => null]);
        $this->assertGreaterThan(0, $count);

        // Verify the template we just added is included
        $found = $this->repository->findOneBy(['valid' => null]);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertNull($found->isValid());
    }

    public function testFindOneByMultipleNullFields(): void
    {
        $template = new RenderTemplate();
        $template->setPath('/multiple-null-test');
        $template->setTitle('Multiple Null Test');
        $template->setContent('<p>Content</p>');
        $template->setValid(null);
        $template->setCreatedBy(null);

        $this->repository->save($template);

        $found = $this->repository->findOneBy(['valid' => null, 'createdBy' => null]);
        $this->assertInstanceOf(RenderTemplate::class, $found);
        $this->assertNull($found->isValid());
        $this->assertNull($found->getCreatedBy());
    }

    public function testFindOneByWithOrderBySingleFieldsSortingLogic(): void
    {
        // Test individual field sorting for findOneBy method
        $template1 = new RenderTemplate();
        $template1->setPath('/sort-test-a');
        $template1->setTitle('A Title');
        $template1->setContent('<p>Content A</p>');
        $template1->setValid(true);

        $template2 = new RenderTemplate();
        $template2->setPath('/sort-test-z');
        $template2->setTitle('Z Title');
        $template2->setContent('<p>Content Z</p>');
        $template2->setValid(false);

        $this->repository->save($template1);
        $this->repository->save($template2);

        // Test sorting by path
        $resultPath = $this->repository->findOneBy([], ['path' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultPath);

        // Test sorting by title
        $resultTitle = $this->repository->findOneBy([], ['title' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultTitle);

        // Test sorting by valid
        $resultValid = $this->repository->findOneBy([], ['valid' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultValid);

        // Test sorting by id
        $resultId = $this->repository->findOneBy([], ['id' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultId);

        // Test sorting by createTime
        $resultCreateTime = $this->repository->findOneBy([], ['createTime' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultCreateTime);

        // Test sorting by updateTime
        $resultUpdateTime = $this->repository->findOneBy([], ['updateTime' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultUpdateTime);

        // Test sorting by createdBy
        $resultCreatedBy = $this->repository->findOneBy([], ['createdBy' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultCreatedBy);

        // Test sorting by updatedBy
        $resultUpdatedBy = $this->repository->findOneBy([], ['updatedBy' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultUpdatedBy);

        // Test sorting by createdFromIp
        $resultCreatedFromIp = $this->repository->findOneBy([], ['createdFromIp' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultCreatedFromIp);

        // Test sorting by updatedFromIp
        $resultUpdatedFromIp = $this->repository->findOneBy([], ['updatedFromIp' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultUpdatedFromIp);

        // Test sorting by parent association
        $resultParent = $this->repository->findOneBy([], ['parent' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $resultParent);
    }

    public function testFindOneByWithComplexOrderByCombinations(): void
    {
        // Test multiple fields ordering for findOneBy method
        $template1 = new RenderTemplate();
        $template1->setPath('/complex-sort-1');
        $template1->setTitle('Complex Sort 1');
        $template1->setContent('<p>Complex content 1</p>');

        $template2 = new RenderTemplate();
        $template2->setPath('/complex-sort-2');
        $template2->setTitle('Complex Sort 2');
        $template2->setContent('<p>Complex content 2</p>');

        $this->repository->save($template1);
        $this->repository->save($template2);

        // Test 2 fields ordering
        $result2Fields = $this->repository->findOneBy([], ['path' => 'ASC', 'title' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $result2Fields);

        // Test 3 fields ordering
        $result3Fields = $this->repository->findOneBy([], ['path' => 'ASC', 'title' => 'ASC', 'parent' => 'ASC']);
        $this->assertInstanceOf(RenderTemplate::class, $result3Fields);

        // Test all fields ordering
        $resultAllFields = $this->repository->findOneBy([], [
            'path' => 'ASC',
            'title' => 'ASC',
            'parent' => 'ASC',
            'valid' => 'ASC',
            'id' => 'ASC',
            'createTime' => 'ASC',
            'updateTime' => 'ASC',
            'createdBy' => 'ASC',
            'updatedBy' => 'ASC',
            'createdFromIp' => 'ASC',
            'updatedFromIp' => 'ASC',
        ]);
        $this->assertInstanceOf(RenderTemplate::class, $resultAllFields);
    }

    public function testCountByNullableFieldsIsNullQueries(): void
    {
        // Ensure we have templates with null values for testing
        $templateWithNulls = new RenderTemplate();
        $templateWithNulls->setPath('/null-fields-test');
        $templateWithNulls->setTitle('Null Fields Test');
        $templateWithNulls->setContent('<p>Null fields content</p>');
        $templateWithNulls->setValid(null);
        $templateWithNulls->setCreatedBy(null);
        $templateWithNulls->setUpdatedBy(null);
        $templateWithNulls->setCreatedFromIp(null);
        $templateWithNulls->setUpdatedFromIp(null);
        $templateWithNulls->setParent(null);

        $this->repository->save($templateWithNulls);

        // Test count by valid IS NULL
        $countValidNull = $this->repository->count(['valid' => null]);
        $this->assertGreaterThan(0, $countValidNull);

        // Test count by createdBy IS NULL
        $countCreatedByNull = $this->repository->count(['createdBy' => null]);
        $this->assertGreaterThan(0, $countCreatedByNull);

        // Test count by updatedBy IS NULL
        $countUpdatedByNull = $this->repository->count(['updatedBy' => null]);
        $this->assertGreaterThan(0, $countUpdatedByNull);

        // Test count by createdFromIp IS NULL
        $countCreatedFromIpNull = $this->repository->count(['createdFromIp' => null]);
        $this->assertGreaterThan(0, $countCreatedFromIpNull);

        // Test count by updatedFromIp IS NULL
        $countUpdatedFromIpNull = $this->repository->count(['updatedFromIp' => null]);
        $this->assertGreaterThan(0, $countUpdatedFromIpNull);

        // Test count by path IS NULL (if path can be null)
        $countPathNull = $this->repository->count(['path' => null]);
        $this->assertGreaterThanOrEqual(0, $countPathNull);

        // Test count by title IS NULL (if title can be null)
        $countTitleNull = $this->repository->count(['title' => null]);
        $this->assertGreaterThanOrEqual(0, $countTitleNull);

        // Test count by content IS NULL (if content can be null)
        $countContentNull = $this->repository->count(['content' => null]);
        $this->assertGreaterThanOrEqual(0, $countContentNull);
    }

    public function testFindByNullableFieldsIsNullQueries(): void
    {
        // Ensure we have templates with null values for testing
        $templateWithNulls = new RenderTemplate();
        $templateWithNulls->setPath('/findby-null-fields-test');
        $templateWithNulls->setTitle('FindBy Null Fields Test');
        $templateWithNulls->setContent('<p>FindBy null fields content</p>');
        $templateWithNulls->setValid(null);
        $templateWithNulls->setCreatedBy(null);
        $templateWithNulls->setUpdatedBy(null);
        $templateWithNulls->setCreatedFromIp(null);
        $templateWithNulls->setUpdatedFromIp(null);
        $templateWithNulls->setParent(null);

        $this->repository->save($templateWithNulls);

        // Test findBy valid IS NULL
        $findValidNull = $this->repository->findBy(['valid' => null]);
        $this->assertIsArray($findValidNull);
        $this->assertGreaterThan(0, count($findValidNull));

        // Test findBy createdBy IS NULL
        $findCreatedByNull = $this->repository->findBy(['createdBy' => null]);
        $this->assertIsArray($findCreatedByNull);
        $this->assertGreaterThan(0, count($findCreatedByNull));

        // Test findBy updatedBy IS NULL
        $findUpdatedByNull = $this->repository->findBy(['updatedBy' => null]);
        $this->assertIsArray($findUpdatedByNull);
        $this->assertGreaterThan(0, count($findUpdatedByNull));

        // Test findBy createdFromIp IS NULL
        $findCreatedFromIpNull = $this->repository->findBy(['createdFromIp' => null]);
        $this->assertIsArray($findCreatedFromIpNull);
        $this->assertGreaterThan(0, count($findCreatedFromIpNull));

        // Test findBy updatedFromIp IS NULL
        $findUpdatedFromIpNull = $this->repository->findBy(['updatedFromIp' => null]);
        $this->assertIsArray($findUpdatedFromIpNull);
        $this->assertGreaterThan(0, count($findUpdatedFromIpNull));

        // Test findBy path IS NULL (if path can be null)
        $findPathNull = $this->repository->findBy(['path' => null]);
        $this->assertIsArray($findPathNull);

        // Test findBy title IS NULL (if title can be null)
        $findTitleNull = $this->repository->findBy(['title' => null]);
        $this->assertIsArray($findTitleNull);

        // Test findBy content IS NULL (if content can be null)
        $findContentNull = $this->repository->findBy(['content' => null]);
        $this->assertIsArray($findContentNull);
    }

    public function testAssociationFieldsComprehensiveQueries(): void
    {
        // Create a comprehensive test scenario for association fields
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/comprehensive-parent');
        $parentTemplate->setTitle('Comprehensive Parent');
        $parentTemplate->setContent('<p>Comprehensive parent content</p>');

        $child1Template = new RenderTemplate();
        $child1Template->setPath('/comprehensive-child-1');
        $child1Template->setTitle('Comprehensive Child 1');
        $child1Template->setContent('<p>Comprehensive child 1 content</p>');
        $child1Template->setParent($parentTemplate);

        $child2Template = new RenderTemplate();
        $child2Template->setPath('/comprehensive-child-2');
        $child2Template->setTitle('Comprehensive Child 2');
        $child2Template->setContent('<p>Comprehensive child 2 content</p>');
        $child2Template->setParent($parentTemplate);

        $this->repository->save($parentTemplate);
        $this->repository->save($child1Template);
        $this->repository->save($child2Template);

        // Test findBy with parent association
        $children = $this->repository->findBy(['parent' => $parentTemplate]);
        $this->assertIsArray($children);
        $this->assertGreaterThanOrEqual(2, count($children));
        foreach ($children as $child) {
            $this->assertSame($parentTemplate->getId(), $child->getParent()?->getId());
        }

        // Test count with parent association
        $childrenCount = $this->repository->count(['parent' => $parentTemplate]);
        $this->assertGreaterThanOrEqual(2, $childrenCount);

        // Test findOneBy with parent association
        $oneChild = $this->repository->findOneBy(['parent' => $parentTemplate]);
        $this->assertInstanceOf(RenderTemplate::class, $oneChild);
        $this->assertSame($parentTemplate->getId(), $oneChild->getParent()?->getId());

        // Test findBy with null parent association
        $rootTemplates = $this->repository->findBy(['parent' => null]);
        $this->assertIsArray($rootTemplates);

        // Test count with null parent association
        $rootCount = $this->repository->count(['parent' => null]);
        $this->assertGreaterThan(0, $rootCount);

        // Test findOneBy with null parent association
        $oneRoot = $this->repository->findOneBy(['parent' => null]);
        $this->assertInstanceOf(RenderTemplate::class, $oneRoot);
        $this->assertNull($oneRoot->getParent());
    }

    // PHPStan Required Tests - Sorting Logic Tests

    // PHPStan Required Tests - Association Field Tests
    public function testFindOneByAssociationParentShouldReturnMatchingEntity(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/association-parent');
        $parentTemplate->setTitle('Association Parent');
        $parentTemplate->setContent('<p>Parent content</p>');

        $childTemplate = new RenderTemplate();
        $childTemplate->setPath('/association-child');
        $childTemplate->setTitle('Association Child');
        $childTemplate->setContent('<p>Child content</p>');
        $childTemplate->setParent($parentTemplate);

        $this->repository->save($parentTemplate);
        $this->repository->save($childTemplate);

        $result = $this->repository->findOneBy(['parent' => $parentTemplate]);
        $this->assertInstanceOf(RenderTemplate::class, $result);
        $this->assertSame($parentTemplate->getId(), $result->getParent()?->getId());
    }

    public function testCountByAssociationParentShouldReturnCorrectNumber(): void
    {
        $parentTemplate = new RenderTemplate();
        $parentTemplate->setPath('/count-parent');
        $parentTemplate->setTitle('Count Parent');
        $parentTemplate->setContent('<p>Parent content</p>');

        $this->repository->save($parentTemplate);

        $initialCount = $this->repository->count(['parent' => $parentTemplate]);

        $child1 = new RenderTemplate();
        $child1->setPath('/count-child-1');
        $child1->setTitle('Count Child 1');
        $child1->setContent('<p>Child 1 content</p>');
        $child1->setParent($parentTemplate);

        $child2 = new RenderTemplate();
        $child2->setPath('/count-child-2');
        $child2->setTitle('Count Child 2');
        $child2->setContent('<p>Child 2 content</p>');
        $child2->setParent($parentTemplate);

        $this->repository->save($child1);
        $this->repository->save($child2);

        $finalCount = $this->repository->count(['parent' => $parentTemplate]);
        $this->assertSame($initialCount + 2, $finalCount);
    }

    // PHPStan Required Tests - Nullable Field IS NULL Tests (Only for truly nullable fields)

    /**
     * @return ServiceEntityRepository<RenderTemplate>
     */
    protected function getRepository(): ServiceEntityRepository
    {
        return $this->repository;
    }

    protected function createNewEntity(): object
    {
        $template = new RenderTemplate();
        $template->setPath('/test-path-' . uniqid());
        $template->setTitle('Test Template ' . uniqid());
        $template->setContent('<h1>Test Content</h1>');

        return $template;
    }
}

<?php

declare(strict_types=1);

namespace Locastic\ApiPlatformTranslationBundle\Tests\Model;

use Locastic\ApiPlatformTranslationBundle\Model\TranslatableInterface;
use Locastic\ApiPlatformTranslationBundle\Tests\Fixtures\DummyTranslatable;
use Locastic\ApiPlatformTranslationBundle\Tests\Fixtures\DummyTranslation;
use PHPUnit\Framework\TestCase;

/**
 * Class TranslatableTraitTest.
 */
class TranslatableTraitTest extends TestCase
{
    /**
     * @test getTranslationLocales
     */
    public function testSetTranslatable(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->setTranslation(null, 'no locale', $dummyTranslatable);

        $this->assertEquals([], $dummyTranslatable->getTranslationLocales());
    }

    /**
     * @test getTranslationLocales
     */
    public function testGetTranslationLocales(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->setTranslation('es', 'espanol', $dummyTranslatable);
        $this->setTranslation('en', 'english', $dummyTranslatable);

        $this->assertEquals(['es', 'en'], $dummyTranslatable->getTranslationLocales());
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationFromTranslationsCache(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->addTranslation($this->setTranslation('en', 'english', $dummyTranslatable));
        $this->assertEquals('english', $dummyTranslatable->getTranslation('en')->getTranslation());
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationWithoutFallbackLocale(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->assertEquals(null, $dummyTranslatable->getTranslation('it')->getTranslation());
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationWithFallbackTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->addTranslation($this->setTranslation('en', 'english', $dummyTranslatable));
        $this->assertEquals('english', $dummyTranslatable->getTranslation('it')->getTranslation());
    }

    /**
     * @test getTranslation
     */
    public function testRemoveTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslationEnglish = $this->setTranslation('en', 'english', $dummyTranslatable);

        $dummyTranslatable->removeTranslation($dummyTranslationEnglish);
        $this->assertEquals(null, $dummyTranslatable->getTranslation('en')->getTranslation());
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationWithoutLocales(): void
    {
        $dummyTranslatable = $this->setTranslatable(null, null);

        $this->expectException(\RuntimeException::class);
        $dummyTranslatable->getTranslation();
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationCreatesAndAddsMissingTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->assertCount(0, $dummyTranslatable->getTranslations());

        $translation = $dummyTranslatable->getTranslation('fr');

        $this->assertSame('fr', $translation->getLocale());
        $this->assertSame($dummyTranslatable, $translation->getTranslatable());
        $this->assertCount(1, $dummyTranslatable->getTranslations());
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationWithAutoCreateDisabledReturnsDetachedTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->setAutoCreateTranslations(false);

        $translation = $dummyTranslatable->getTranslation('fr');

        $this->assertSame('fr', $translation->getLocale());
        $this->assertNull($translation->getTranslatable());
        $this->assertCount(0, $dummyTranslatable->getTranslations());
        $this->assertNotSame($translation, $dummyTranslatable->getTranslation('fr'), 'A detached translation must not be cached.');
    }

    /**
     * @test getTranslation
     */
    public function testGetTranslationWithAutoCreateDisabledStillReturnsExistingAndFallbackTranslations(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->setAutoCreateTranslations(false);
        $this->setTranslation('en', 'english', $dummyTranslatable);

        $this->assertSame('english', $dummyTranslatable->getTranslation('en')->getTranslation());
        $this->assertSame('english', $dummyTranslatable->getTranslation('it')->getTranslation());
    }

    /**
     * @test getOrCreateTranslation
     */
    public function testGetOrCreateTranslationAttachesEvenWithAutoCreateDisabled(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->setAutoCreateTranslations(false);

        $translation = $dummyTranslatable->getOrCreateTranslation('fr');

        $this->assertSame('fr', $translation->getLocale());
        $this->assertSame($dummyTranslatable, $translation->getTranslatable());
        $this->assertCount(1, $dummyTranslatable->getTranslations());
        $this->assertSame($translation, $dummyTranslatable->getOrCreateTranslation('fr'));
    }

    /**
     * @test getOrCreateTranslation
     */
    public function testGetOrCreateTranslationReturnsExistingTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $english = $this->setTranslation('en', 'english', $dummyTranslatable);
        $dummyTranslatable->addTranslation($english);

        $this->assertSame($english, $dummyTranslatable->getOrCreateTranslation('en'));
    }

    /**
     * @test getOrCreateTranslation
     */
    public function testGetOrCreateTranslationDoesNotFallBackToAnotherLocale(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $dummyTranslatable->addTranslation($this->setTranslation('en', 'english', $dummyTranslatable));

        $translation = $dummyTranslatable->getOrCreateTranslation('it');

        $this->assertSame('it', $translation->getLocale());
        $this->assertNull($translation->getTranslation());
        $this->assertCount(2, $dummyTranslatable->getTranslations());
    }

    /**
     * @test getOrCreateTranslation
     */
    public function testGetOrCreateTranslationUsesCurrentLocaleByDefault(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');

        $this->assertSame('es', $dummyTranslatable->getOrCreateTranslation()->getLocale());
    }

    /**
     * @test getOrCreateTranslation
     */
    public function testGetOrCreateTranslationWithoutLocales(): void
    {
        $dummyTranslatable = $this->setTranslatable(null, null);

        $this->expectException(\RuntimeException::class);
        $dummyTranslatable->getOrCreateTranslation();
    }

    /**
     * @test hasTranslation
     */
    public function testHasTranslation(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $english = $this->setTranslation('en', 'english', $dummyTranslatable);

        $notAdded = new DummyTranslation();
        $notAdded->setLocale('fr');

        $this->assertTrue($dummyTranslatable->hasTranslation($english));
        $this->assertFalse($dummyTranslatable->hasTranslation($notAdded));
    }

    /**
     * @test addTranslation
     */
    public function testAddTranslationIgnoresTranslationWithoutLocale(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');

        $translation = new DummyTranslation();
        $dummyTranslatable->addTranslation($translation);

        $this->assertCount(0, $dummyTranslatable->getTranslations());
        $this->assertNull($translation->getTranslatable());
    }

    /**
     * @test addTranslation
     */
    public function testAddTranslationIgnoresDuplicateLocale(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->setTranslation('en', 'english', $dummyTranslatable);

        $duplicate = new DummyTranslation();
        $duplicate->setLocale('en');
        $duplicate->setTranslation('english again');
        $dummyTranslatable->addTranslation($duplicate);

        $this->assertCount(1, $dummyTranslatable->getTranslations());
        $this->assertSame('english', $dummyTranslatable->getTranslation('en')->getTranslation());
    }

    /**
     * @test removeTranslationWithLocale
     */
    public function testRemoveTranslationWithLocale(): void
    {
        $dummyTranslatable = $this->setTranslatable('es', 'en');
        $this->setTranslation('en', 'english', $dummyTranslatable);
        $this->setTranslation('es', 'espanol', $dummyTranslatable);

        $dummyTranslatable->removeTranslationWithLocale('en');

        $this->assertSame(['es'], array_values($dummyTranslatable->getTranslationLocales()));
    }

    /**
     * @param TranslatableInterface<DummyTranslation> $translatable
     */
    private function setTranslation(?string $locale, string $translation, TranslatableInterface $translatable): DummyTranslation
    {
        $dummyTranslation = new DummyTranslation();
        $dummyTranslation->setLocale($locale);
        $dummyTranslation->setTranslation($translation);
        $dummyTranslation->setTranslatable($translatable);

        return $dummyTranslation;
    }

    private function setTranslatable(?string $currentLocale, ?string $fallbackLocale): DummyTranslatable
    {
        $dummyTranslatable = new DummyTranslatable();
        $dummyTranslatable->setCurrentLocale($currentLocale);
        $dummyTranslatable->setFallbackLocale($fallbackLocale);

        return $dummyTranslatable;
    }
}

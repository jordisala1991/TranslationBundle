<?php

declare(strict_types=1);

/*
 * This file is part of the TranslationBundle.
 *
 * (c) Runroom <runroom@runroom.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Runroom\TranslationBundle\Tests\Unit;

use Doctrine\ORM\EntityRepository;
use PHPUnit\Framework\TestCase;
use Runroom\TranslationBundle\Service\TranslationService;
use Runroom\TranslationBundle\Tests\Fixtures\TranslationFixtures;
use Symfony\Component\Translation\TranslatorInterface;

class TranslationServiceTest extends TestCase
{
    private $repository;
    private $translator;
    private $service;

    protected function setUp(): void
    {
        $this->repository = $this->prophesize(EntityRepository::class);
        $this->translator = $this->prophesize(TranslatorInterface::class);

        $this->service = new TranslationService(
            $this->repository->reveal(),
            $this->translator->reveal()
        );
    }

    /**
     * @test
     */
    public function itReturnsAStringTranslatedByTheRepository()
    {
        $translation = TranslationFixtures::create();

        $this->repository->findOneBy(['key' => TranslationFixtures::KEY])->willReturn($translation);
        $this->translator->trans(TranslationFixtures::KEY, [], null, 'en')->shouldNotBeCalled();

        $result = $this->service->translate(TranslationFixtures::KEY, [], 'en');

        $this->assertSame(TranslationFixtures::VALUE, $result);
    }

    /**
     * @test
     */
    public function itReturnsAStringTranslatedByTheTranslatorComponent()
    {
        $this->repository->findOneBy(['key' => TranslationFixtures::KEY])->willReturn(null);
        $this->translator->trans(TranslationFixtures::KEY, [], null, 'en')
            ->willReturn('another_translation');

        $result = $this->service->translate(TranslationFixtures::KEY, [], 'en');

        $this->assertSame('another_translation', $result);
    }
}

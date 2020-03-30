<?php
declare(strict_types=1);

namespace Beecom\Balikobot\Model\Resolver;

use Beecom\Balikobot\Model\Resolver\DataProvider\Branch as PageDataProvider;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

/**
 * CMS testimonials field resolver, used for GraphQL request processing
 */
class Branch implements ResolverInterface
{
    /**
     * @var PageDataProvider
     */
    protected $branchDataProvider;

    /**
     *
     * @param PageDataProvider $dataProvider
     */
    public function __construct(
        PageDataProvider $dataProvider
    ) {
        $this->branchDataProvider = $dataProvider;
    }

    /**
     * @inheritdoc
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {

        try {
            $header = $this->branchDataProvider->getBranches($args);
        } catch (NoSuchEntityException $e) {
            throw new GraphQlNoSuchEntityException(__($e->getMessage()), $e);
        }
        return $header;
    }
}

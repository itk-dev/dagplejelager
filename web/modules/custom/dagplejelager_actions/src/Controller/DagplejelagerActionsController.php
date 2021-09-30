<?php

namespace Drupal\dagplejelager_actions\Controller;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\Product;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provide actions for Dagplejelager.
 */
class DagplejelagerActionsController extends ControllerBase {

  /**
   * The cart manager.
   *
   * @var \Drupal\commerce_cart\CartManagerInterface
   */
  protected $cartManager;

  /**
   * The cart provider.
   *
   * @var \Drupal\commerce_cart\CartProviderInterface
   */
  protected $cartProvider;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new CartController object.
   *
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider, EntityTypeManagerInterface $entityTypeManager) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * Add a bundle of products to the cart.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $commerce_product
   *   The product bundle.
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   The redirect.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function actionAddBundle(ProductInterface $commerce_product, Request $request) {
    $storeId = $commerce_product->get('stores')->getValue()[0]['target_id'];
    $store = $this->entityTypeManager()
      ->getStorage('commerce_store')
      ->load($storeId);

    /** @var \Drupal\commerce_store\Entity\StoreInterface|void $store */
    $cart = $this->cartProvider->getCart('default', $store);
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }
    if ($commerce_product->hasField('field_product_reference') && !empty($commerce_product->field_product_reference->getValue())) {
      foreach ($commerce_product->field_product_reference->getValue() as $product_id) {
        $this->addProductVariationToCart($product_id['target_id'], $cart);
      }
    }

    // Set redirect. (Original path.)
    $referer = $request->server->get('HTTP_REFERER');
    $response = new RedirectResponse($referer);

    return $response;
  }

  /**
   * Add a product variation to the cart.
   *
   * @param int $product_id
   *   THe product id.
   * @param \Drupal\commerce_order\Entity\OrderInterface $cart
   *   The cart entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function addProductVariationToCart(int $product_id, OrderInterface $cart) {
    $productObj = Product::load($product_id);
    $product_variation_id = $productObj->get('variations')->getValue()[0]['target_id'];
    $variationObject = $this->entityTypeManager()
      ->getStorage('commerce_product_variation')
      ->load($product_variation_id);

    /** @var \Drupal\commerce\PurchasableEntityInterface $variationObject */
    $this->cartManager->addEntity($cart, $variationObject);
  }

}

<?php

namespace Drupal\dagplejelager_actions\Controller;

use Drupal\commerce_cart\CartManagerInterface;
use Drupal\commerce_cart\CartProviderInterface;
use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\commerce_product\Entity\ProductInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Url;
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
   * Constructs a new CartController object.
   *
   * @param \Drupal\commerce_cart\CartManagerInterface $cart_manager
   *   The cart manager.
   * @param \Drupal\commerce_cart\CartProviderInterface $cart_provider
   *   The cart provider.
   */
  public function __construct(CartManagerInterface $cart_manager, CartProviderInterface $cart_provider) {
    $this->cartManager = $cart_manager;
    $this->cartProvider = $cart_provider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('commerce_cart.cart_manager'),
      $container->get('commerce_cart.cart_provider'),
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
    $stores = $commerce_product->get('stores');
    assert($stores instanceof EntityReferenceFieldItemList);
    $stores = $stores->referencedEntities();
    $store = reset($stores);
    $cart = $this->cartProvider->getCart('default', $store);
    if (!$cart) {
      $cart = $this->cartProvider->createCart('default', $store);
    }

    if ($commerce_product->hasField('field_product_reference')) {
      $products = $commerce_product->get('field_product_reference');
      assert($products instanceof EntityReferenceFieldItemList);
      $products = $products->referencedEntities();

      foreach ($products as $product) {
        $this->addProductVariationToCart($product, $cart);
      }
    }

    // Set redirect. (Original path.)
    $referer = $request->server->get('HTTP_REFERER');
    $response = new RedirectResponse($referer);

    return $response;
  }

  /**
   * Convert an order to a cart.
   */
  public function convertOrderToCart(OrderInterface $order) {
    if ('validation' !== $order->getState()->getId()) {
      $this->messenger()->addError($this->t('Only orders with state "validation" can be edited'));
      $url = Url::fromRoute('entity.commerce_order.user_view', [
        'user' => $order->getCustomerId(),
        'commerce_order' => $order->id(),
      ]);
    }
    else {
      $order
        ->set('state', 'draft')
        ->set('cart', TRUE)
        ->set('checkout_step', NULL)
        ->save();

      $url = Url::fromRoute('commerce_cart.page');
    }

    return new RedirectResponse($url->toString());
  }

  /**
   * Add a product variation to the cart.
   *
   * @param \Drupal\commerce_product\Entity\ProductInterface $product
   *   THe product id.
   * @param \Drupal\commerce_order\Entity\OrderInterface $cart
   *   The cart entity.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function addProductVariationToCart(ProductInterface $product, OrderInterface $cart) {
    $productVariations = $product->get('variations');
    assert($productVariations instanceof EntityReferenceFieldItemList);
    $productVariations = $productVariations->referencedEntities();
    $this->cartManager->addEntity($cart, reset($productVariations));
  }

}

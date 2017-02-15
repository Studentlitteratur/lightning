<?php

namespace Drupal\lightning_media;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\PluginBase;
use Drupal\media_entity\MediaBundleInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for media bundle resolvers.
 */
abstract class BundleResolverBase extends PluginBase implements BundleResolverInterface, ContainerFactoryPluginInterface {

  use SourceFieldTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * BundleResolverBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getPossibleBundles() {
    $plugin_definition = $this->getPluginDefinition();

    $filter = function (MediaBundleInterface $bundle) use ($plugin_definition) {
      $field = $this->getSourceFieldForBundle($bundle);
      return $field ? in_array($field->getType(), $plugin_definition['field_types']) : FALSE;
    };

    $all_bundles = $this->entityTypeManager
      ->getStorage('media_bundle')
      ->loadMultiple();

    return array_filter($all_bundles, $filter);
  }

}

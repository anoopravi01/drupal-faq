<?php

/**
 * @file
 * Contains \Drupal\faq\Plugin\Block\FaqCategoriesBlock.
 */

namespace Drupal\faq\Plugin\Block;

use Drupal\faq\FaqHelper;
use Drupal\block\BlockBase;
use Drupal\taxonomy\Entity\Vocabulary;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Annotation\Translation;

/**
 * Provides a simple block.
 *
 * @Block(
 *   id = "faq_categories",
 *   admin_label = @Translation("FAQ Categories")
 * )
 */
class FaqCategoriesBlock extends BlockBase {

  /**
   * Implements \Drupal\block\BlockBase::blockBuild().
   */
  public function build() {
    static $vocabularies, $terms;
    $items = array();

    $faq_settings = \Drupal::config('faq.settings');
    if(!$faq_settings->get('use_categories')) {
      return;
    }
    $moduleHandler = \Drupal::moduleHandler();
    
    if ($moduleHandler->moduleExists('taxonomy')) {
      if (!isset($terms)) {
        $terms = array();
        $vocabularies = Vocabulary::loadMultiple();
        $vocab_omit = array_flip($faq_settings->get('omit_vocabulary'));
        $vocabularies = array_diff_key($vocabularies, $vocab_omit);
        foreach ($vocabularies as $vocab) {
          foreach (taxonomy_get_tree($vocab->vid) as $term) {
            if (FaqHelper::taxonomyTermCountNodes($term->tid)) {
              $terms[$term->name] = $term->tid;
            }
          }
        }
      }
      if (count($terms) > 0) {
        foreach ($terms as $name => $tid) {
          $items[] = l($name, 'faq-page/' . $tid);
        }
      }
    }
    return array(
      '#theme' => 'item_list',
      '#items' => $items,
      '#list_type' => $faq_settings->get('category_listing'),
    );
  }

}

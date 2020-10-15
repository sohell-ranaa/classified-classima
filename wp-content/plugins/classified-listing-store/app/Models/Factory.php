<?php

namespace RtclStore\Models;

use Rtcl\Helpers\Cache;

class Factory
{
    /**
     * Get a Store.
     *
     * @param bool $store_id
     *
     * @return Store|bool Store object or null if the store cannot be loaded.
     */
    public function get_store($store_id = false) {
        $store_id = $this->get_store_id($store_id);
        if (!$store_id) {
            return false;
        }

        try {
            return new Store($store_id);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param bool $store
     *
     * @return bool|int
     */
    private function get_store_id($store = false) {
        global $post;

        if (false === $store && isset($post, $post->ID) && rtclStore()->post_type === get_post_type($post->ID)) {
            return absint($post->ID);
        } elseif (is_numeric($store)) {
            return $store;
        } elseif ($store instanceof Store) {
            return $store->get_the_id();
        } elseif ($store instanceof \WP_Post) {
            return $store->ID;
        } elseif (!empty($store->ID)) {
            return $store->ID;
        } else {
            return false;
        }
    }

    /**
     * Get a Store.
     *
     * @param bool $user_id
     *
     * @return Membership|bool Membership object or null if the store cannot be loaded.
     */
    public function get_membership($user_id = false) {
        $user_id = $this->get_user_id($user_id);
        if (!$user_id) {
            return false;
        }

        try {
            $cache_key = Cache::get_cache_prefix('membership') . 'member_' . $user_id;
            $membership = wp_cache_get($cache_key, 'membership');

            if (false === $membership) {
                $membership = new Membership($user_id);
                wp_cache_set($cache_key, $membership, 'membership');
            }
            return $membership;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * @param bool $membership
     *
     * @return bool|int
     */
    private function get_user_id($membership = false) {
        if (false === $membership) {
            return get_current_user_id();
        } elseif (is_numeric($membership)) {
            return $membership;
        } elseif ($membership instanceof Membership) {
            return $membership->get_user_id();
        } else {
            return false;
        }
    }

}
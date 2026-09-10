<?php
// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

namespace local_assess_type;

/**
 * Global configuration support class
 *
 * @package     local_assess_type
 * @copyright   2026 onwards University College London {@link https://www.ucl.ac.uk/}
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @author      Amanda Doughty <m.doughty@ucl.ac.uk>
 */
class config {
    /** @var \stdClass config */
    protected \stdClass $config;

    /**
     * Constructor
     *
     */
    private function __construct() {
        $this->config = get_config('local_assess_type');
    }

    /**
     * Singleton instance
     *
     * @param bool $forcenew
     * @return self
     */
    public static function instance(bool $forcenew = false): self {
        static $instance;
        if (!$instance || $forcenew) {
            $instance = new static();
        }
        return $instance;
    }

    /**
     * Is this plugin enabled?
     * @return bool
     */
    public function is_enabled(): bool {
        return (bool)$this->config->enabled;
    }

    /**
     * Get the list of enabled LTI ID's as an array
     * @return int[]
     */
    public function get_lti_types(): array {
        return array_filter(array_map('intval', explode(',', ($this->config->ltitypes ?? ''))));
    }

    /**
     * Get possible LTI types
     *
     * @return array [id => name]
     */
    public static function get_all_lti_types(): array {
        global $DB;

        return $DB->get_records_menu('lti_types', null, '', 'id, name');
    }
}

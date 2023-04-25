<?php
class Elementor_Podcast_Player_Widget_Advanced extends \Elementor\Widget_Base {

	public function get_name() {
		return 'podcast-player-widget-advanced';
	}

	public function get_title() {
		return 'RSS Display';
	}

	public function get_icon() {
		return 'eicon-code';
	}

	public function get_categories() {
		return [ 'basic' ];
	}

	public function get_keywords() {
		return [ 'podcast', 'podcasts', 'gallery', 'library' ];
	}

	protected function register_controls() {
		// Content Tab Start

		global $wp_roles;
		$roles = $wp_roles->get_names();
		unset($roles['administrator']);

		$this->start_controls_section(
			'section_title',
			[
				'label' => esc_html__('Podcast Player', 'podcast-player-widget-advanced'),
				'tab' => \Elementor\Controls_Manager::TAB_CONTENT,
			]
		);

		$this->add_control(
			'rss_feeds',
			[
				'label' => __( 'RSS Feeds', 'podcast-player-widget-advanced' ),
				'type' => \Elementor\Controls_Manager::REPEATER,
				'fields' => [
					[
						'name' => 'rssfeedtitle',
						'label' => __( 'Podcast Category Title', 'podcast-player-widget-advanced' ),
						'type' => \Elementor\Controls_Manager::TEXT,
					],
					[
						'name' => 'url',
						'label' => __( 'Podcast RSS URL', 'podcast-player-widget-advanced' ),
						'type' => \Elementor\Controls_Manager::TEXT,
					],
					[
						'name' => 'protectedcontent',
						'label' => __( 'Protected Content?', 'podcast-player-widget-advanced' ),
						'type' => \Elementor\Controls_Manager::SELECT,
						'options' => [
							'default' => esc_html__( 'Default', 'podcast-player-widget-advanced' ),
							'yes' => esc_html__( 'Yes', 'podcast-player-widget-advanced' ),
							'no' => esc_html__( 'No', 'podcast-player-widget-advanced' ),
						],
						'default' => 'no',
					],
					[
						'name' => 'allowed_roles',
						'label' => __( 'Allowed User Roles', 'podcast-player-widget-advanced' ),
						'type' => \Elementor\Controls_Manager::SELECT2,
						'options' => $roles,
						'multiple' => true,
						'condition' => [
							'protectedcontent' => 'yes',
						],
					],
				],
				//'title_field' => '{{{ element.rssfeedtitle }}}',
				'default' => [
					[
						'rssfeedtitle' => '',
						'url' => '',
						'protectedcontent' => 'no',
						'allowed_roles' => [],
					],
				],
			]
		);
		$this->add_control(
			'podcastamount',
			[
				'label' => __('Number of Podcasts', 'podcast-player-widget-advanced'),
				'type' => \Elementor\Controls_Manager::NUMBER,
				'default' => 6,
				'min' => 1,
				'max' => 100,
				'step' => 1,
				'separator' => 'before',
			]
		);

		$this->end_controls_section();
	}
	protected function render() {
        $settings = $this->get_settings_for_display();
        $current_user = wp_get_current_user();
        $user_roles = $current_user->roles;
        $rss_feeds = $settings['rss_feeds'];
        $podcastLimit = $settings['podcastamount'];

        $data = array();
        foreach ($rss_feeds as $index => $feed) {
            $podcastURL = $feed['url'];
            $rss = fetch_feed($podcastURL);

            if (!is_wp_error($rss)) {
                $maxitems = $rss->get_item_quantity($podcastLimit);
                $rss_items = $rss->get_items(0, $maxitems);

                $items = array();
                foreach ($rss_items as $item) {
                    $items[] = array(
                        'title' => $item->get_title(),
                        'enclosure' => $item->get_enclosure()->get_link(),
                        'duration' => $item->get_enclosure()->get_duration(),
                    );
                }
                $data[] = array(
                    'rssfeedtitle' => $feed['rssfeedtitle'],
                    'url' => $feed['url'],
                    'protectedcontent' => $feed['protectedcontent'],
                    'allowed_roles' => $feed['allowed_roles'],
                    'items' => $items,
                );
            }
        }

        wp_localize_script('audio_player_script', 'elementorPodcastData', array(
            'podcasts' => $data,
            'userRoles' => $user_roles,
        ));

        // Audio player outside the accordion
        ?>
<div class="container">
	<div id="audio-player">
		<div id="current-title" class="current-title"></div>
		<audio id="player" data-audio-limit="<?php echo $podcastLimit; ?>"></audio>
		<div id="controls">
			<button id="play" class="control"><i class="fas fa-play"></i></button>
			<button id="pause" class="control"><i class="fas fa-pause"></i></button>
			<button id="stop" class="control"><i class="fas fa-stop"></i></button>
		</div>
		<div id="seeker">
			<input type="range" id="seekbar" value="0" step="1" min="0">
			<div class="current-time" id="current-time"></div>
			<div id="duration"></div>
		</div>
	</div>

	<div class="custom-accordion-container"></div>
</div>
<?php
    }
}

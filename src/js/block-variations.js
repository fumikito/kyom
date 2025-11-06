/**
 * Block Variations
 *
 * @package kyom
 */

wp.domReady(() => {
	// Netabareブロック（Groupブロックのバリエーション）
	wp.blocks.registerBlockVariation('core/group', {
		name: 'netabare',
		title: 'ネタバレ',
		description: 'クリックするまで内容がぼかされて表示されるブロックです。',
		category: 'kyom',
		icon: 'hidden',
		attributes: {
			className: 'netabare',
		},
		innerBlocks: [
			['core/paragraph', {placeholder: 'ネタバレの内容を入力...'}],
		],
		scope: ['inserter'],
	});
});

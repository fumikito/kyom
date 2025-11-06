import { __ } from '@wordpress/i18n';
import { useBlockProps, InspectorControls, MediaUpload, MediaUploadCheck, RichText } from '@wordpress/block-editor';
import { PanelBody, TextControl, SelectControl, Button } from '@wordpress/components';
import { ColorPicker } from '@wordpress/components';
import './editor.scss';

export default function Edit({ attributes, setAttributes }) {
	const { title, background, textColor, textBackground, align, content } = attributes;

	// 背景画像URLを取得
	const backgroundUrl = background ? wp.media.attachment(background).get('url') : '';

	const blockProps = useBlockProps({
		style: {
			backgroundImage: backgroundUrl ? `url(${backgroundUrl})` : 'none',
			backgroundSize: 'cover',
			backgroundPosition: 'center',
			minHeight: '400px',
			display: 'flex',
			alignItems: 'center',
			justifyContent: 'center',
		},
	});

	return (
		<>
			<InspectorControls>
				<PanelBody title={__('Hero Settings', 'kyom')} initialOpen={true}>
					<TextControl
						label={__('Title', 'kyom')}
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						help={__('Leave empty to use site name', 'kyom')}
					/>
					<MediaUploadCheck>
						<MediaUpload
							onSelect={(media) => setAttributes({ background: media.id })}
							allowedTypes={['image']}
							value={background}
							render={({ open }) => (
								<div style={{ marginBottom: '16px' }}>
									<label style={{ display: 'block', marginBottom: '8px', fontWeight: 600 }}>
										{__('Background Image', 'kyom')}
									</label>
									<Button onClick={open} variant="secondary">
										{background ? __('Change Image', 'kyom') : __('Select Image', 'kyom')}
									</Button>
									{background && (
										<Button
											onClick={() => setAttributes({ background: 0 })}
											variant="link"
											isDestructive
											style={{ marginLeft: '8px' }}
										>
											{__('Remove', 'kyom')}
										</Button>
									)}
								</div>
							)}
						/>
					</MediaUploadCheck>
					<div style={{ marginBottom: '16px' }}>
						<label style={{ display: 'block', marginBottom: '8px', fontWeight: 600 }}>
							{__('Text Color', 'kyom')}
						</label>
						<ColorPicker
							color={textColor}
							onChangeComplete={(value) => setAttributes({ textColor: value.hex })}
						/>
					</div>
					<div style={{ marginBottom: '16px' }}>
						<label style={{ display: 'block', marginBottom: '8px', fontWeight: 600 }}>
							{__('Text Background Color', 'kyom')}
						</label>
						<ColorPicker
							color={textBackground}
							onChangeComplete={(value) => setAttributes({ textBackground: value.hex })}
						/>
					</div>
					<SelectControl
						label={__('Text Align', 'kyom')}
						value={align}
						options={[
							{ label: __('Center', 'kyom'), value: 'center' },
							{ label: __('Left', 'kyom'), value: 'left' },
							{ label: __('Right', 'kyom'), value: 'right' },
						]}
						onChange={(value) => setAttributes({ align: value })}
					/>
				</PanelBody>
			</InspectorControls>

			<div {...blockProps}>
				<div style={{
					color: textColor,
					textShadow: textBackground ? `0 0 5px ${textBackground}` : null,
					padding: '20px',
					textAlign: align,
				}}>
					<RichText
						tagName="h1"
						value={title}
						onChange={(value) => setAttributes({ title: value })}
						placeholder={__('Enter title...', 'kyom')}
						style={{ margin: 0, marginBottom: content ? '16px' : 0 }}
					/>
					<RichText
						tagName="div"
						value={content}
						onChange={(value) => setAttributes({ content: value })}
						placeholder={__('Enter content...', 'kyom')}
						style={{ margin: 0 }}
					/>
				</div>
			</div>
		</>
	);
}

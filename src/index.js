import { registerPlugin } from '@wordpress/plugins';
import { PluginPostStatusInfo } from '@wordpress/edit-post';
import { useSelect } from '@wordpress/data';
import { __ } from '@wordpress/i18n';
import { dateI18n, __experimentalGetSettings } from '@wordpress/date';
import { useEntityProp } from '@wordpress/core-data';

const OriginalDatePreservedInfo = () => {
	const postType = useSelect(
		(select) => select('core/editor').getCurrentPostType(),
		[]
	);

	const [meta] = useEntityProp('postType', postType, 'meta');
	const originalDate = meta && meta['_dp_original_post_date'];

	if (!originalDate) {
		return null;
	}

	const dateSettings = __experimentalGetSettings();
	const formattedDate = dateI18n(
		'F j g:i a',
		originalDate
	);

	return (
		<PluginPostStatusInfo>
			<div className="notice notice-info inline">
				<p style={{ margin: 0 }}>
					{__('The original publish date will be preserved as', 'text-domain')} <strong>{formattedDate}</strong>.
				</p>
			</div>
		</PluginPostStatusInfo>
	);
};

registerPlugin('duplicate-post-preserve-date', { render: OriginalDatePreservedInfo });

/**
 * Handles delete all confirmation
 *
 * @author Tedy Lim <tedyjd@gmail.com>
 * @date 06.01.2019
 * @package gridfieldcustom
 * @subpackage javascript
 */
(function($){
	$.entwine('ss', function($) {

		$('#action_deleteall').entwine({
			onclick: function(e) {
				if (this.data('confirm') && !confirm(this.data('confirm'))) {
					e.preventDefault();
					e.stopPropagation();
					return false;
				}
				this._super(e);
			}
		});

	});
})(jQuery);

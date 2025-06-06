<?php
/**
 * /_includes/layout/asset-footer.inc.php
 *
 * This file is part of DomainMOD, an open source domain and internet asset manager.
 * Copyright (c) 2010-2025 Greg Chetcuti <greg@greg.ca>
 *
 * Project: http://domainmod.org   Author: https://greg.ca
 *
 * DomainMOD is free software: you can redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the License, or (at your option) any later
 * version.
 *
 * DomainMOD is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even the implied
 * warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License along with DomainMOD. If not, see
 * http://www.gnu.org/licenses/.
 *
 */
?>
<?php if ($_SESSION['s_display_inactive_assets'] != "1") { ?>

    <em><?php echo _('Inactive Assets are currently hidden.'); ?> <a href="<?php echo $web_root; ?>/settings/toggles/inactive-assets/"><?php echo _('Click here to display them'); ?></a>.</em><BR><?php

} else { ?>

    <em><?php echo _('Inactive Assets are currently displayed.'); ?> <a href="<?php echo $web_root; ?>/settings/toggles/inactive-assets/"><?php echo _('Click here to hide them'); ?></a>.</em><BR><?php

}

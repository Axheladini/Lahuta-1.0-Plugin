
<div class="lahuta-gallery">
    <div class="lahuta-gallery__menu">
        <div
        class="nav nav-pills flex-column"
        id="settings-tabs"
        role="tablist"
        aria-orientation="vertical"
        >
           <!-- <button
                class="nav-link "
                id="galleries-tab"
                data-bs-toggle="pill"
                data-bs-target="#galleries"
                type="button"
                role="tab"
                aria-controls="galleries"
                aria-selected="true"
            >
            <?php /* echo __('Galleries', 'lahuta-plugin'); */ ?>
            </button> -->

            <button
                class="nav-link active"
                id="add-gallery-tab"
                data-bs-toggle="pill"
                data-bs-target="#add-gallery"
                type="button"
                role="tab"
                aria-controls="add-gallery"
                aria-selected="true"
            >
            <?php echo __('Manage Galleries', 'lahuta-plugin'); ?>
            </button>

            <button
                class="nav-link"
                id="gallery-categories-tab"
                data-bs-toggle="pill"
                data-bs-target="#gallery-categories"
                type="button"
                role="tab"
                aria-controls="gallery-categories"
                aria-selected="false"
            >
            <?php echo __('Gallery Categories', 'lahuta-plugin'); ?>
            </button>
        </div>
    </div>

    <div class="lahuta-gallery__content tab-content">
        <div
            class="tab-pane fade show active lahuta-gallery__pane"
            id="add-gallery"
            role="tabpanel"
            aria-labelledby="add-gallery-tab"
            tabindex="0"
            >
            <!-- Include the add gallery template --> 
             <?php include LAHUTA_PLUGIN_DIR . 'includes/templates/lahuta-gallery-add-new.php'; ?>
            <!-- Gallery structure ENDS here -->
        </div>

        <div
            class="tab-pane fade lahuta-gallery__pane"
            id="gallery-categories"
            role="tabpanel"
            aria-labelledby="gallery-categories-tab"
            tabindex="0"
            >
            <h4 class="lahuta-h4"><?php echo __('Manage your gallery categories.', 'lahuta-plugin'); ?></h4>

            <!-- Include the gallery category management template --> 
            <?php include LAHUTA_PLUGIN_DIR . 'includes/templates/lahuta-gallery-category-management.php'; ?>
            <!-- Gallery structure ENDS here -->
        </div>
    </div>
</div>
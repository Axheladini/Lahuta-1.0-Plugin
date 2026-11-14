<div class="lahuta-gallery-cat-mng container-fluid">
    <div class="row">
        <div class="col-md-7">
            <p><?php esc_html_e( 'Fill in the details below to create a new gallery.', 'lahuta-plugin' ); ?></p>
            <div id="lahuta-gallery-status" class="mb-3" role="status" aria-live="polite"></div>
            <div class="lahuta-category-search mb-3 lahuta-search">
                <label for="lahuta-gallery-search" class="form-label lahuta-search">
                    <?php esc_html_e( 'Filter by gallery title', 'lahuta-rest-api' ); ?>
                </label>
                <input type="search" id="lahuta-gallery-search" class="form-control lahuta-search__input" autocomplete="off">
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover align-middle lahuta-table">
                    <thead class="table-light lahuta-table__head">
                        <tr class="lahuta-table__head--row">
                            <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Image', 'lahuta-rest-api' ); ?></th>
                            <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Gallery', 'lahuta-rest-api' ); ?></th>
                            <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Category', 'lahuta-rest-api' ); ?></th>
                            <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Images', 'lahuta-rest-api' ); ?></th>
                            <th class="lahuta-table__head--cell text-end" scope="col"><?php esc_html_e( 'Actions', 'lahuta-rest-api' ); ?></th>
                        </tr>
                    </thead>
                    <tbody class="lahuta-table__body" id="lahuta-gallery-list">
                        <tr class="lahuta-table__body--row">
                            <td class="lahuta-table__body--cell" colspan="5"><?php esc_html_e( 'Loading galleries...', 'lahuta-rest-api' ); ?></td>
                        </tr>
                    </tbody>
                </table>
            </div>
            <nav aria-label="<?php esc_attr_e( 'Gallery pages', 'lahuta-rest-api' ); ?>">
                <ul id="lahuta-gallery-pagination" class="pagination justify-content-center"></ul>
            </nav>
            <p id="lahuta-gallery-page-status" class="text-muted text-center" aria-live="polite"></p>
        </div>
        <div class="col-md-5 lahuta-form-holder">
            <div class="card mb-6 lahuta-card">
                <h4 class="lahuta-h4" id="lahuta-gallery-form-title"><?php esc_html_e( 'Add new gallery', 'lahuta-rest-api' ); ?></h4>
                <form id="lahuta-gallery-add-new-form" class="lahuta-form" novalidate>
                    <input type="hidden" id="galleryId" value="">
                    <div id="lahuta-gallery-message" class="alert d-none" role="alert"></div>
                    <div class="form-group">
                        <label for="galleryTitle" class="lahuta-form__label"><?php esc_html_e( 'Gallery Title', 'lahuta-rest-api' ); ?></label>
                        <input type="text" name="galleryTitle" id="galleryTitle" class="form-control lahuta-form__input" aria-describedby="galleryTitleError">
                        <small id="galleryTitleError" class="form-text text-muted lahuta-form__error">
                            <?php esc_html_e( 'Gallery title is required and must be between 5 and 200 characters.', 'lahuta-rest-api' ); ?>
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="galleryDescription" class="lahuta-form__label"><?php esc_html_e( 'Gallery Description', 'lahuta-rest-api' ); ?></label>
                        <textarea name="galleryDescription" id="galleryDescription" class="form-control lahuta-form__input" aria-describedby="galleryDescriptionError"></textarea>
                        <small id="galleryDescriptionError" class="form-text text-muted lahuta-form__error">
                            <?php esc_html_e( 'Gallery description is required and must be between 5 and 500 characters.', 'lahuta-rest-api' ); ?>
                        </small>
                    </div>
                    <div class="form-group">
                        <label for="galleryCategory" class="lahuta-form__label"><?php esc_html_e( 'Gallery Category', 'lahuta-rest-api' ); ?></label>
                        <select name="galleryCategory" id="galleryCategory" class="form-control lahuta-form__input lahuta-form__input--select">
                            <option value="-1"><?php esc_html_e( 'None', 'lahuta-rest-api' ); ?></option>
                            <?php foreach ( $gallery_categories as $category ) : ?>
                                <option value="<?php echo esc_attr( $category['id'] ); ?>">
                                    <?php echo esc_html( $category['name'] ); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <small id="galleryCategoryError" class="form-text text-muted lahuta-form__error">
                            <?php esc_html_e( 'Gallery category is required.', 'lahuta-rest-api' ); ?>
                        </small>
                    </div>
                    <div class="form-group">
                        <label class="lahuta-form__label"><?php esc_html_e( 'Gallery images', 'lahuta-rest-api' ); ?></label>
                        <div id="lahuta-gallery-images" class="lahuta-gallery-images lahuta-form__upload-wrapper">
                            <input name="galleryImages" type="file" class="form-control" id="galleryImages" accept="image/*" multiple hidden>
                            <label id="galleryImagesChooseButton" for="galleryImages" class="lahuta-form__upload-btn">
                                <i class="bi bi-image" aria-hidden="true"></i>
                                <?php esc_html_e( 'Choose gallery images', 'lahuta-rest-api' ); ?>
                            </label>
                            <small id="lahutaMainImageError" class="form-text text-danger fw-semibold lahuta-form__error" role="alert" aria-live="polite"></small>
                            <div id="lahuta-gallery-images-list" class="lahuta-gallery-images__list mt-3"></div>
                        </div>
                    </div>
                    <div class="form-group pt-4">
                        <button type="submit" class="btn btn-primary lahuta-form__input lahuta-form__input--submit d-none" id="lahuta-gallery-submit" hidden>
                            <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                            <span class="lahuta-gallery-submit__label"><?php esc_html_e( 'Add Gallery', 'lahuta-rest-api' ); ?></span>
                        </button>
                        <button type="button" class="btn btn-secondary lahuta-form__input lahuta-form__input--delete" id="lahuta-cancel-gallery-edit" hidden>
                            <?php esc_html_e( 'Cancel edit', 'lahuta-rest-api' ); ?>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="lahuta-gallery-cat-mng  container-fluid">
  <div class="row">
    <div class="col-md-7">
        <div id="lahuta-gallery-category-status" class="mb-3" role="status" aria-live="polite"></div>
        <div class="lahuta-category-search mb-3 lahuta-search">
            <label for="lahuta-gallery-category-search" class="form-label lahuta-search">
                <?php esc_html_e( 'Filter by category name', 'lahuta-rest-api' ); ?>
            </label>
            <input
                type="search"
                id="lahuta-gallery-category-search"
                class="form-control lahuta-search__input"
                autocomplete="off"
            >
        </div>
        <div class="table-responsive">
            <table class="table table-striped table-hover align-middle lahuta-table">
                <thead class="table-light lahuta-table__head" >
                    <tr class="lahuta-table__head--row">
                        <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Category', 'lahuta-rest-api' ); ?></th>
                        <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Parent', 'lahuta-rest-api' ); ?></th>
                        <th class="lahuta-table__head--cell" scope="col"><?php esc_html_e( 'Description', 'lahuta-rest-api' ); ?></th>
                        <th class="lahuta-table__head--cell text-end" scope="col"><?php esc_html_e( 'Actions', 'lahuta-rest-api' ); ?></th>
                    </tr>
                </thead>
                <tbody class="lahuta-table__body"
                    id="lahuta-gallery-category-list"
                    data-categories="<?php echo esc_attr( wp_json_encode( $gallery_categories ) ); ?>" >
                    <tr class="lahuta-table__body--row">
                        <td class="lahuta-table__body--cell" colspan="4"><?php esc_html_e( 'Loading categories...', 'lahuta-rest-api' ); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
        <nav aria-label="<?php esc_attr_e( 'Gallery category pages', 'lahuta-rest-api' ); ?>">
            <ul id="lahuta-gallery-category-pagination" class="pagination justify-content-center"></ul>
        </nav>
        <p id="lahuta-gallery-category-page-status" class="text-muted text-center" aria-live="polite"></p>
    </div>
    <div class="col-md-5 lahuta-form-holder">
        <div class="card mb-6 lahuta-card">
            <h4 class="lahuta-h4"><?php esc_html_e( 'Add / edit gallery category', 'lahuta-rest-api' ); ?></h4>
            <form id="lahuta-gallery-category-form" class="lahuta-form" >
                    <input type="hidden" id="categoryTermId" value="">
                    <div class="form-group">
                    <label for="categoryName" class="lahuta-form__label"><?php esc_html_e( 'Category name', 'lahuta-rest-api' ); ?></label>
                    <input type="text" name="categoryName" id="categoryName" class="form-control lahuta-form__input lahuta-form__input--cat" aria-describedby="categoryName">
                    <small id="categoryNameError" class="form-text text-muted lahuta-form__error">Category name is required and must be between 5 and 200 characters.</small>
                </div>
                    <div class="form-group">
                    <label for="categoryDescription" class="lahuta-form__label"><?php esc_html_e( 'Description', 'lahuta-rest-api' ); ?></label>
                    <textarea name="categoryDescription" id="categoryDescription" class="form-control lahuta-form__input lahuta-form__input--desc" rows="3"></textarea>
                    <small id="categoryDescriptionError" class="form-text text-muted lahuta-form__error">Description is required and must not exceed 500 characters.</small>
                </div>
                <div class="form-group">
                    <label for="categoryParent" class="lahuta-form__label"><?php esc_html_e( 'Category parent', 'lahuta-rest-api' ); ?></label>
                        <?php if ( '' !== $gallery_category_load_error ) : ?>
                            <div class="alert alert-danger" role="alert"><?php echo esc_html( $gallery_category_load_error ); ?></div>
                        <?php endif; ?>
                        <select name="categoryParent" id="categoryParent" class="form-control lahuta-form__input lahuta-form__input--select">
                        <option value="-1"><?php esc_html_e( 'None', 'lahuta-rest-api' ); ?></option>
                        <?php foreach ( $gallery_categories as $category ) : ?>
                            <option value="<?php echo esc_attr( $category['id'] ); ?>">
                                <?php echo esc_html( $category['name'] ); ?>
                            </option>
                        <?php endforeach; ?>
                        </select>
                </div>
                <div class="form-group lahuta-category-form__actions pt-4">
                    <button type="submit" class="btn btn-primary lahuta-form__input lahuta-form__input--submit" id="lahuta-create-gallery-category"
                            data-add-label="<?php esc_attr_e( 'Create Category', 'lahuta-rest-api' ); ?>"
                            data-update-label="<?php esc_attr_e( 'Update Category', 'lahuta-rest-api' ); ?>"
                            data-loading-label="<?php esc_attr_e( 'Saving category...', 'lahuta-rest-api' ); ?>">
                        <span class="spinner-border spinner-border-sm me-2 d-none" aria-hidden="true"></span>
                        <span class="lahuta-create-gallery-category__label"><?php esc_html_e( 'Create Category', 'lahuta-rest-api' ); ?></span>
                        <span class="visually-hidden" role="status"><?php esc_html_e( 'Adding category...', 'lahuta-rest-api' ); ?></span>
                    </button>
                    <button type="button" class="btn btn-secondary lahuta-form__input lahuta-form__input--delete" id="lahuta-cancel-category-edit" hidden>
                        <?php esc_html_e( 'Cancel edit', 'lahuta-rest-api' ); ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
  </div>
</div>
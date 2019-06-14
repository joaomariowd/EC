<?php
namespace EC\Helpers;

class Html5 {
	static function FormPAlert($errors, $spacer = NULL){
        
		$spacer = is_null($spacer) ? 'mt-2' : $spacer;

		$r = "<p class='alert alert-warning $spacer'>";

		foreach ($errors as $key=>$error) {
			$r .= $key . ' ' . $error[0] . '<br>';
		}

		$r .= '</p>';

		return $r;
    }
    
    static function inputs ($label = NULL, $id, $type, $obj = NULL, $placeholder = NULL) {
		$value = (is_object($obj)) ? $obj->$id : NULL;
		$label = (is_null($label)) ? NULL : "<label for = '$id'>$label</label>";
		return
			"<div class = 'form-group'>" .
			$label .
			"<input type = '$type' class = 'form-control' id = '$id' name = '$id' placeholder = '$placeholder' value= '$value'>" .
			"</div>";
	}

	static function button ($title, $type, $class = NULL) {
		return "<div class='form-group'><button type='$type' class='btn $class'>$title</button></div>";
    }
    
    static function pagination ($collection, $class = NULL) {
        $result = "<nav aria-label='page navigation'>";
		$result .= "<ul class='pagination pagination-sm isset($class) ? $class : NULL'>";

        $result .= "<li class='page-item " . $collection->page == $collection->previous() ? 'disabled' : NULL  . "'>";
        return $result;
	    $result .= "<a data-page='<?=$collection->previous()?>' class='page-link' href='<?=$collection->previousSlang()'><span aria-hidden='true'>&laquo;</span><span class='sr-only'>Previous</span></a></li>";

        return $result;
        
        /*$result .= "if $collection->page > $collection->first()}
				<li class="page-item">
					<a data-page="{$collection->first()}" class="page-link" href="{$collection->firstSlang()}">
						{$collection->first()}
					</a>
				</li>
			{/if}

			{if $collection->previous(10) > 1}
				<li class="page-item">
					<a data-page="{$collection->previous(10)}" class="page-link" href="{$collection->previousSlang(10)}">
						{$collection->previous(10)}
					</a>
				</li>
			{/if}

			{if $collection->previous(5) > 1}
				<li class="page-item">
					<a data-page="{$collection->previous(5)}" class="page-link" href="{$collection->previousSlang(5)}">
						{$collection->previous(5)}
					</a>
				</li>
			{/if}

			{if $collection->previous() > 1}
				<li class="page-item">
					<a data-page="{$collection->previous()}" class="page-link" href="{$collection->previousSlang()}">
						{$collection->previous()}
					</a>
				</li>
			{/if}

			<li class="page-item active">
				<a data-page="{$collection->page}" class="page-link">{$collection->page}</a>
			</li>

			{if $collection->next() < $collection->lastPage}
				<li class="page-item">
					<a data-page="{$collection->next()}" class="page-link" href="{$collection->nextSlang()}">
						{$collection->next()}
					</a>
				</li>
			{/if}

			{if $collection->next(5) < $collection->lastPage}
				<li class="page-item">
					<a data-page="{$collection->next(5)}" class="page-link" href="{$collection->nextSlang(5)}">
						{$collection->next(5)}
					</a>
				</li>
			{/if}

			{if $collection->next(10) < $collection->lastPage}
				<li class="page-item">
					<a data-page="{$collection->next(10)}" class="page-link" href="{$collection->nextSlang(10)}">
						{$collection->next(10)}
					</a>
				</li>
			{/if}

			{if $collection->page < $collection->lastPage}
				<li class="page-item">
					<a data-page="{$collection->last()}" class="page-link" href="{$collection->lastSlang()}">
						{$collection->last()}
					</a>
				</li>
			{/if}

			<li class="page-item {if $collection->page == $collection->next()}disabled{/if}">
				<a data-page="{$collection->next()}" class="page-link" href="{$collection->nextSlang()}">
					<span aria-hidden="true">&raquo;</span>
					<span class="sr-only">Next</span>
				</a>
			</li>
		</ul>
	</nav>*/
    }
}
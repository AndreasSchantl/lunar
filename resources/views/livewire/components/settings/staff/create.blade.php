<div>

  <div class="flex-col space-y-4">
    <form wire:submit.prevent="create" method="POST" class="space-y-4">
      @include('adminhub::partials.forms.staff.fields')
    </form>
  </div>
</div>

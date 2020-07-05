
<div class="dropdown">
    <button class="btn btn-block dropdown-toggle" type="button" id="dropdownMenuButton" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
        {{\Dnsoftware\Dncurrency\Models\Dncurrencies::$current_currency}}
    </button>
    <div class="dropdown-menu" aria-labelledby="dropdownMenuButton">

        <form class="px-4 py-3" action="/dncurrency/changecurrency">

            @lang('dncurrency::dncurrency.currency')

            <select name="currencycode">
                @foreach ($currencies as $ckey => $cval)
                    @php
                        $selected = '';
                        if ($cval->code == \Dnsoftware\Dncurrency\Models\Dncurrencies::$current_currency) {
                            $selected = 'selected';
                        }
                    @endphp
                    <option value="{{$cval->code}}" {{$selected}}>{{$cval->langdata[$locale]['full']}}</option>

                @endforeach
            </select>

            <div style="margin-top: 10px;">
            <button type="submit" class="btn btn-primary">@lang('dncurrency::dncurrency.save')</button>
            </div>
        </form>


    </div>
</div>
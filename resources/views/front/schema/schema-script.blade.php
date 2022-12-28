@section('scripts')
    @parent
    <script>
        $(function () {
            $.ajaxSetup({
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });

            let totals = $("#totals");
            let totalBase = $("#total-base");

            $(".seats").click(function (e) {
                e.preventDefault();

                let place = $(this);
                let event_id = '{{ $event->id }}';
                let colId = place.data('col-id');
                let rowId = place.data('row-id');
                let seat = place.data('seat');
                let row = place.data('row');
                let placeName = place.data('name');
                let uId = place.attr('data-uid');
                let classItem = place.attr('class');

                let formData = new FormData();
                formData.append('event_id', '{{ $event->id }}');
                formData.append('place_name', placeName);
                formData.append('col_id', colId);
                formData.append('row_id', rowId);
                formData.append('seat', seat);
                formData.append('row', row);


                if(classItem.includes('busy') == false) {

                    if ($(this).hasClass('cart-reserved'))
                    {
                        $(this).removeClass('active');
                        $(this).removeClass('cart-reserved');
                        let uId = colId+'{{ $event->id }}';
                        formData.append('u_id', uId);
                        // remvoe from cart
                        $.ajax({
                            type: "POST",
                            url: '{{ route('cart.api.delete').'?uid=' }}'+uId,
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (response) {
                                if (response.success == true) {
                                    place.attr('data-uid', '');

                                    totals.empty();
                                    let object = response.totals;
                                    for (var prop in object) {
                                        totals.append('<span>' + object[prop] + ' {{ $vars['spectacle_map_tickets_for'] }} ' + prop + ', </span>');
                                    }

                                    totalBase
                                        .empty()
                                        .append('<span>' + response.total + ' {{ $vars['spectacle_lei'] }} </span>')

                                } else {
                                    alert('Something went wrong');
                                }
                            },
                            error: function (response) {
                                console.log(response);
                            }
                        });
                    }
                    else
                    {
                        formData.append('u_id', uId);
                        $.ajax({
                            type: "POST",
                            url: '{{ route('cart.toggle') }}',
                            data: formData,
                            cache: false,
                            contentType: false,
                            processData: false,
                            success: function (response) {
                                if (response.success == true) {
                                    if (response.u_id == null) {
                                        place.attr('class', classItem.replace('active', ''));
                                        place.attr('data-uid', '');

                                    } else {
                                        place.attr('class', classItem + ' active');
                                        place.attr('data-uid', response.u_id);
                                    }

                                    totals.empty();
                                    let object = response.totals;
                                    for (var prop in object) {
                                        totals.append('<span>' + object[prop] + ' {{ $vars['spectacle_map_tickets_for'] }} ' + prop + ', </span>');
                                    }

                                    totalBase
                                        .empty()
                                        .append('<span>' + response.total + ' {{ $vars['spectacle_lei'] }} </span>')

                                } else {
                                    alert('Something went wrong');
                                }
                            },
                            error: function (response) {
                                console.log(response);
                            }
                        });
                    }

                }
            });

        });
    </script>
@endsection
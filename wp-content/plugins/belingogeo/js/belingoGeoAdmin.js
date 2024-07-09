jQuery(function ($) {

  $('#belingo_geo_basic_default_nonecity').select2({
    ajax: {
      url: ajaxurl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term,
          action: 'getcitiescallback'
        };
      },
      processResults: function (data) {
        var options = [];
        if (data) {
          $.each(data, function (index, text) {
            options.push({
              id: text[0], text: text[1]
            });
          });
        }
        return {
          results: options
        };
      },
      cache: false
    },
    minimumInputLength: 2
  });

  $('#belingo_geo_exclude_posts').select2({
    ajax: {
      url: ajaxurl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term,
          action: 'getpostscallback'
        };
      },
      processResults: function (data) {
        var options = [];
        if (data) {
          $.each(data, function (index, text) {
            options.push({
              id: text[0], text: text[1]
            });
          });
        }
        return {
          results: options
        };
      },
      cache: false
    },
    minimumInputLength: 3
  });

  $('#belingo_geo_exclude_pages').select2({
    ajax: {
      url: ajaxurl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term,
          action: 'getpagescallback'
        };
      },
      processResults: function (data) {
        var options = [];
        if (data) {
          $.each(data, function (index, text) {
            options.push({
              id: text[0], text: text[1]
            });
          });
        }
        return {
          results: options
        };
      },
      cache: false
    },
    minimumInputLength: 0
  });

  $('#belingo_geo_exclude_terms').select2({
    ajax: {
      url: ajaxurl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term,
          action: 'gettermscallback'
        };
      },
      processResults: function (data) {
        var options = [];
        if (data) {
          $.each(data, function (index, text) {
            options.push({
              id: text[0], text: text[1]
            });
          });
        }
        return {
          results: options
        };
      },
      cache: false
    },
    minimumInputLength: 0
  });

  $('#belingo_geo_exclude_tags').select2({
    ajax: {
      url: ajaxurl,
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return {
          q: params.term,
          action: 'gettagscallback'
        };
      },
      processResults: function (data) {
        var options = [];
        if (data) {
          $.each(data, function (index, text) {
            options.push({
              id: text[0], text: text[1]
            });
          });
        }
        return {
          results: options
        };
      },
      cache: false
    },
    minimumInputLength: 0
  });
});
jQuery(window).ready(function($){
   var result_examples = {
       findResults: function(){
           var $h5 = $('h5');
           var results = [];
           $.map($h5, function(node, index) {
               var element = $(node);
               if (element.prop('id').indexOf('result') !== -1) {
                   results.push(element);
               }
           });

           return results;
       },
       addCollapseMarkup: function(results){
           $.map(results, function(node, index) {
               var element = $(node);
               var code = element.nextUntil('pre').last().next();

               var markup = '<div class="collapse" id="' + element.prop('id') + '-tab"><div class="well"><pre>'+ code.clone().html() + '</pre></div></div>';

               $(markup).insertAfter(code);

               if (code.is( 'pre')) {
                   var button = '<button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#' + element.prop('id') + '-tab" aria-expanded="false" aria-controls="' + element.prop('id') + '-tab">View example result</button>';
                   $(button).insertAfter(element.next());
                   code.remove();
               }
           });

       },
       highlightJS: function() {
           hljs.configure({
               tabReplace: '    '
           });

           $('pre code').each(function(i, block) {
               hljs.highlightBlock(block);
           });
       }
   }

    result_examples.addCollapseMarkup(result_examples.findResults());
    result_examples.highlightJS();
});
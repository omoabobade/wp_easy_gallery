/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
jQuery(document).ready(function($) {

                    $('#slideshow').desoSlide({
                                thumbs: $('#slideshow_thumbs li > a'),
                                overlay: 'always',
                                controls: {
                                    show: true,
                                    keys: true
                                }
                            });
                            
                            $('.img-responsive').click(function(){
                                    the_thumb = $(this);
                                     $('.img-responsive').attr('style','opacity:0.3;'); 
                                    the_thumb.attr('style','opacity:1;');                            
                                });
 });
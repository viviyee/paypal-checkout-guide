paypal
  .Buttons({
    createOrder: function (data, actions) {
      /* modify code here
      you need to pass an id.
      get that id here.
      */
      return fetch(`/paypal/orders/${id}`)
        .then((response) => response.json())
        .then((order) => {
          console.log('order created')
          return order.id
        })
    },

    onApprove: function (data, actions) {
      console.log('order approved')
      
      return fetch(`/paypal/orders/${data.orderID}/capture`)
        .then((response) => response.json())
        .then((order) => {
          console.log('order captured', order)
          
          /* you can add codes here, if you want to do something after the successful paypal transaction (redirecting or showing success messages)
          */
        });
    },
  })
  .render("#paypal-button-container");

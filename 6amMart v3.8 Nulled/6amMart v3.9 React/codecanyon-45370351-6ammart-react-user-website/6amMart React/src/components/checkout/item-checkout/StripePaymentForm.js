import React, { useState, forwardRef, useImperativeHandle } from "react";
import {
  PaymentElement,
  Elements,
  useStripe,
  useElements,
} from "@stripe/react-stripe-js";
import { loadStripe } from "@stripe/stripe-js";
import { CustomStackFullWidth } from "styled-components/CustomStyles.style";
import { Typography } from "@mui/material";
import { useTranslation } from "react-i18next";
import MainApi from "api-manage/MainApi";

let stripePromiseCache = null;
export const getStripePromise = (publishableKey) => {
  if (!stripePromiseCache && publishableKey) {
    stripePromiseCache = loadStripe(publishableKey);
  }
  return stripePromiseCache;
};

const InnerForm = forwardRef(({ totalAmount, onError }, ref) => {
  const stripe = useStripe();
  const elements = useElements();
  const { t } = useTranslation();
  const [errorMessage, setErrorMessage] = useState("");

  useImperativeHandle(ref, () => ({
    confirmPayment: async (orderId) => {
      if (!stripe || !elements) {
        return { success: false, error: "Stripe não carregado" };
      }

      setErrorMessage("");

      try {
        // 1. Criar PaymentIntent no backend
        const { data } = await MainApi.post("/stripe-connect/payment-intent", {
          order_id: orderId,
        });

        if (!data?.client_secret) {
          throw new Error(data?.error || "Erro ao criar pagamento");
        }

        // 2. Confirmar pagamento com Stripe
        const { error, paymentIntent } = await stripe.confirmPayment({
          elements,
          clientSecret: data.client_secret,
          confirmParams: {
            return_url: `${window.location.origin}/checkout?payment=success`,
          },
          redirect: "if_required",
        });

        if (error) {
          setErrorMessage(error.message);
          onError?.(error.message);
          return { success: false, error: error.message };
        }

        if (paymentIntent?.status === "succeeded") {
          return { success: true, paymentIntent };
        }

        return { success: true, requiresAction: true };
      } catch (err) {
        const msg = err?.response?.data?.error || err.message || "Erro no pagamento";
        setErrorMessage(msg);
        onError?.(msg);
        return { success: false, error: msg };
      }
    },
  }));

  return (
    <CustomStackFullWidth spacing={2}>
      <Typography fontSize="14px" fontWeight="500">
        {t("Dados do cartão")}
      </Typography>
      <PaymentElement
        options={{
          layout: "tabs",
          wallets: {
            applePay: "auto",
            googlePay: "auto",
          },
        }}
      />
      {errorMessage && (
        <Typography color="error" fontSize="12px">
          {errorMessage}
        </Typography>
      )}
    </CustomStackFullWidth>
  );
});

InnerForm.displayName = "InnerForm";

const StripePaymentForm = forwardRef(({ publishableKey, ...rest }, ref) => {
  const stripe = getStripePromise(publishableKey);

  if (!stripe) {
    return (
      <Typography color="error" fontSize="12px">
        Stripe não configurado
      </Typography>
    );
  }

  return (
    <Elements stripe={stripe}>
      <InnerForm ref={ref} {...rest} />
    </Elements>
  );
});

StripePaymentForm.displayName = "StripePaymentForm";

export default StripePaymentForm;

import { useEffect } from "react";
import {useNavigate, useSearchParams} from "react-router-dom";

export default function VerifyEmail() {
    const [searchParams] = useSearchParams();
    const navigate = useNavigate();
    const token = searchParams.get("token");

    useEffect(() => {
        const verifyEmail = async () => {
            if (!token) {
                navigate("/login?verified=error");
                return;
            }

            try {
                const res = await fetch(`/api/verify-email`, {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ token }),
                });

                const data = await res.json();

                if (data.status === "success") {
                    navigate("/login?verified=success");
                } else {
                    navigate("/login?verified=error");
                }
            } catch (error) {
                navigate("/login?verified=error");
            }
        };

        verifyEmail();
    }, [token, navigate]);

    return null;
}

import React, { useState } from "react";

const initialState = {
    name: "",
    lastName: "",
    email: "",
    password: "",
    confirmPassword: "",
    agreeTerms: false,
};

const passwordPattern = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[\W_]).{8,}$/;

export default function RegistrationForm({ role, onSuccess }) {
    const [form, setForm] = useState(initialState);
    const [fieldErrors, setFieldErrors] = useState({});
    const [submitError, setSubmitError] = useState("");
    const [submitting, setSubmitting] = useState(false);

    const handleChange = (field) => (event) => {
        const value = field === "agreeTerms" ? event.target.checked : event.target.value;
        setForm((prev) => ({
            ...prev,
            [field]: value,
        }));
    };

    const validate = () => {
        const errors = {};
        if (!form.name.trim()) {
            errors.name = "First name is required.";
        } else if (form.name.length > 45) {
            errors.name = "First name cannot exceed 45 characters.";
        }
        if (!form.lastName.trim()) {
            errors.lastName = "Last name is required.";
        } else if (form.lastName.length > 45)
            errors.lastName = "Last name cannot exceed 45 characters."; {
        }
        if (!form.email.trim()) {
            errors.email = "Email is required.";
        } else if (!/^\S+@\S+\.\S+$/.test(form.email)) {
            errors.email = "Enter a valid email address.";
        } else if (form.email.length > 180) {
            errors.email = "Email cannot exceed 180 characters.";
        }
        if (!form.password) {
            errors.password = "Password is required.";
        } else if (form.password.length < 8) {
            errors.password = "Your password should be at least 8 characters.";
        }
        else if (!passwordPattern.test(form.password)) {
            errors.password = "Your password must contain an uppercase letter, lowercase letter, number and special character.";
        }
        if (!form.confirmPassword) {
            errors.confirmPassword = "Please confirm your password.";
        } else if (form.password !== form.confirmPassword) {
            errors.confirmPassword = "Passwords must match.";
        }
        if (!form.agreeTerms) {
            errors.agreeTerms = "You have to agree to the terms and conditions.";
        }
        return errors;
    };

    const handleSubmit = async (event) => {
        event.preventDefault();
        setSubmitError("");
        const errors = validate();
        setFieldErrors(errors);
        if (Object.keys(errors).length > 0) {
            return;
        }

        setSubmitting(true);
        try {
            const response = await fetch(`/api/register`, {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({
                    name: form.name.trim(),
                    lastName: form.lastName.trim(),
                    email: form.email.trim(),
                    password: form.password,
                    agreeTerms: form.agreeTerms,
                    type: role,
                }),
            });

            let data = null;
            const contentType = response.headers.get("Content-Type");
            if (contentType && contentType.includes("application/json")) {
                data = await response.json();
            }

            if (!response.ok) {
                if (data && data.status === "error") {
                    if (data.errors) {
                        setFieldErrors(data.errors);
                    }

                    setSubmitError(data.message || "We couldn't complete your registration.");
                } else {
                    setSubmitError("Unexpected error. Please try again later.");
                }
                return;
            }

            setForm(initialState);
            setFieldErrors({});
            if (onSuccess) {
                onSuccess();
            }
        } catch (error) {
            setSubmitError(error.message || "Unexpected error. Please try again later.");
        } finally {
            setSubmitting(false);
        }
    };

    return (
        <>
            {submitError && (
                <div className="mb-4 bg-red-100 border border-red-400 text-red-700 px-3 py-2 rounded-lg text-sm text-center">
                    {submitError}
                </div>
            )}
            <form onSubmit={handleSubmit} className="space-y-4">
                <div>
                    <label htmlFor="name" className="block text-sm font-medium text-ink mb-1">
                        First Name
                    </label>
                    <input
                        id="name"
                        type="text"
                        value={form.name}
                        onChange={handleChange("name")}
                        className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                        placeholder="First name"
                        required
                    />
                    {fieldErrors.name && (
                        <p className="text-sm text-red-600 mt-1">{fieldErrors.name}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="lastName" className="block text-sm font-medium text-ink mb-1">
                        Last Name
                    </label>
                    <input
                        id="lastName"
                        type="text"
                        value={form.lastName}
                        onChange={handleChange("lastName")}
                        className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                        placeholder="Last name"
                        required
                    />
                    {fieldErrors.lastName && (
                        <p className="text-sm text-red-600 mt-1">{fieldErrors.lastName}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="email" className="block text-sm font-medium text-ink mb-1">
                        Email address
                    </label>
                    <input
                        id="email"
                        type="email"
                        value={form.email}
                        onChange={handleChange("email")}
                        className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                        placeholder="Email"
                        required
                    />
                    {fieldErrors.email && (
                        <p className="text-sm text-red-600 mt-1">{fieldErrors.email}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="password" className="block text-sm font-medium text-ink mb-1">
                        Password
                    </label>
                    <input
                        id="password"
                        type="password"
                        value={form.password}
                        onChange={handleChange("password")}
                        className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                        placeholder="Password"
                        required
                    />
                    {fieldErrors.password && (
                        <p className="text-sm text-red-600 mt-1">{fieldErrors.password}</p>
                    )}
                </div>

                <div>
                    <label htmlFor="confirmPassword" className="block text-sm font-medium text-ink mb-1">
                        Repeat Password
                    </label>
                    <input
                        id="confirmPassword"
                        type="password"
                        value={form.confirmPassword}
                        onChange={handleChange("confirmPassword")}
                        className="form-control w-full rounded-lg border border-gray-300 px-4 py-2 text-sm text-ink focus:border-cobalt focus:ring-cobalt"
                        placeholder="Repeat Password"
                        required
                    />
                    {fieldErrors.confirmPassword && (
                        <p className="text-sm text-red-600 mt-1">{fieldErrors.confirmPassword}</p>
                    )}
                </div>

                <div className="flex items-center justify-between mt-6">
                    <div className="flex items-center space-x-2">
                        <input
                            id="agreeTerms"
                            type="checkbox"
                            checked={form.agreeTerms}
                            onChange={handleChange("agreeTerms")}
                            className="rounded border-gray-300 text-cobalt focus:ring-cobalt"
                        />
                        <label htmlFor="agreeTerms" className="text-sm text-ink">
                            I agree to the terms and conditions.
                        </label>
                    </div>
                    <button
                        type="submit"
                        disabled={submitting}
                        className="cursor-pointer uppercase bg-cobalt text-white text-sm px-8 py-2.5 rounded-lg hover:bg-cobalt-dark transition disabled:opacity-75"
                    >
                        {submitting ? "Processing..." : "Register"}
                    </button>
                </div>
                {fieldErrors.agreeTerms && (
                    <p className="text-sm text-red-600 mt-1">{fieldErrors.agreeTerms}</p>
                )}
            </form>
        </>
    );
}
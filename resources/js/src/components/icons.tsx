import type { SVGProps } from 'react';

// Small hand-rolled icon set (outline style, 1.5px stroke) so the auth/dashboard
// shell doesn't need an icon library dependency for half a dozen glyphs.
type IconProps = SVGProps<SVGSVGElement>;

function base(props: IconProps) {
    return { fill: 'none', stroke: 'currentColor', strokeWidth: 1.5, viewBox: '0 0 24 24', ...props };
}

export function MailIcon(props: IconProps) {
    return (
        <svg {...base(props)}>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.25 6.75c0-.83.67-1.5 1.5-1.5h16.5c.83 0 1.5.67 1.5 1.5v10.5c0 .83-.67 1.5-1.5 1.5H3.75a1.5 1.5 0 0 1-1.5-1.5V6.75Z"
            />
            <path strokeLinecap="round" strokeLinejoin="round" d="m3 7 9 6 9-6" />
        </svg>
    );
}

export function LockIcon(props: IconProps) {
    return (
        <svg {...base(props)}>
            <path strokeLinecap="round" strokeLinejoin="round" d="M16.5 10.5V7.5a4.5 4.5 0 1 0-9 0v3" />
            <rect x="3.75" y="10.5" width="16.5" height="10" rx="1.5" />
        </svg>
    );
}

export function EyeIcon(props: IconProps) {
    return (
        <svg {...base(props)}>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M2.25 12s3.75-7.5 9.75-7.5 9.75 7.5 9.75 7.5-3.75 7.5-9.75 7.5-9.75-7.5-9.75-7.5Z"
            />
            <circle cx="12" cy="12" r="3" />
        </svg>
    );
}

export function EyeOffIcon(props: IconProps) {
    return (
        <svg {...base(props)}>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M3 3l18 18M10.58 10.58a3 3 0 0 0 4.24 4.24M6.53 6.53C4.36 8 2.94 10 2.25 12c0 0 3.75 7.5 9.75 7.5 1.97 0 3.66-.5 5.06-1.24M9.88 4.68A10.6 10.6 0 0 1 12 4.5c6 0 9.75 7.5 9.75 7.5-.47.94-1.2 2.09-2.2 3.19"
            />
        </svg>
    );
}

export function LogOutIcon(props: IconProps) {
    return (
        <svg {...base(props)}>
            <path
                strokeLinecap="round"
                strokeLinejoin="round"
                d="M8.25 8.25V6a2.25 2.25 0 0 1 2.25-2.25h6A2.25 2.25 0 0 1 18.75 6v12a2.25 2.25 0 0 1-2.25 2.25h-6A2.25 2.25 0 0 1 8.25 18v-2.25"
            />
            <path strokeLinecap="round" strokeLinejoin="round" d="M3 12h11.25M11.25 8.25 15 12l-3.75 3.75" />
        </svg>
    );
}

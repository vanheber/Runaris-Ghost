import './bootstrap';
import * as bootstrap from 'bootstrap';
window.bootstrap = bootstrap;

import 'bootstrap-icons/font/bootstrap-icons.css';

import.meta.glob([
    '../assets/images/**',
], { eager: true, as: 'url' });

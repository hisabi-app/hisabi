import {
    Wallet,
    ShoppingCart,
    House,
    Car,
    Lightning,
    FirstAid,
    GraduationCap,
    GameController,
    AirplaneTilt,
    TShirt,
    DeviceMobile,
    Wrench,
    Heart,
    Gift,
    Coffee,
    FilmSlate,
    Barbell,
    Boat,
    Bus,
    Taxi,
    Train,
    Bicycle,
    Dog,
    Cat,
    Pizza,
    Hamburger,
    ForkKnife,
    Wine,
    BeerBottle,
    Martini,
    IceCream,
    Heartbeat,
    Hospital,
    Pill,
    Syringe,
    Bandaids,
    BookOpen,
    Books,
    PencilSimple,
    Backpack,
    CreditCard,
    Bank,
    Coins,
    CurrencyDollar,
    TrendUp,
    ChartLine,
    Buildings,
    BuildingOffice,
    Storefront,
    Baby,
    BabyCarriage,
    PawPrint,
    Tree,
    Plant,
    Flower,
    Sparkle,
    Sun,
    Moon,
    Globe,
    MapPin,
    Briefcase,
    Desktop,
    Laptop,
    Phone,
    EnvelopeSimple,
    Newspaper,
    Article,
    Palette,
    Scissors,
    Needle,
    Hammer,
    PaintBrush,
    Camera,
    MusicNote,
    Microphone,
    Headphones,
    Television,
} from '@phosphor-icons/react';

export interface IconOption {
    name: string;
    component: React.ElementType;
    label: string;
}

// Map icon names to their Phosphor Icon components
export const categoryIconMap: Record<string, React.ElementType> = {
    // Financial
    wallet: Wallet,
    'credit-card': CreditCard,
    bank: Bank,
    coins: Coins,
    'currency-dollar': CurrencyDollar,
    'trend-up': TrendUp,
    'chart-line': ChartLine,

    // Shopping & Retail
    'shopping-cart': ShoppingCart,
    storefront: Storefront,
    gift: Gift,

    // Home & Living
    house: House,
    buildings: Buildings,
    'building-office': BuildingOffice,
    plant: Plant,
    flower: Flower,

    // Transportation
    car: Car,
    bus: Bus,
    taxi: Taxi,
    train: Train,
    bicycle: Bicycle,
    'airplane-tilt': AirplaneTilt,
    boat: Boat,

    // Food & Dining
    utensils: ForkKnife,
    pizza: Pizza,
    hamburger: Hamburger,
    'fork-knife': ForkKnife,
    wine: Wine,
    'beer-bottle': BeerBottle,
    martini: Martini,
    'ice-cream': IceCream,
    coffee: Coffee,

    // Health & Fitness
    'first-aid': FirstAid,
    heartbeat: Heartbeat,
    hospital: Hospital,
    pill: Pill,
    syringe: Syringe,
    bandaids: Bandaids,
    heart: Heart,
    barbell: Barbell,

    // Education
    'graduation-cap': GraduationCap,
    'book-open': BookOpen,
    books: Books,
    'pencil-simple': PencilSimple,
    backpack: Backpack,

    // Entertainment
    'game-controller': GameController,
    'film-slate': FilmSlate,
    television: Television,
    'music-note': MusicNote,
    microphone: Microphone,
    headphones: Headphones,

    // Fashion & Personal
    't-shirt': TShirt,
    scissors: Scissors,
    needle: Needle,
    palette: Palette,

    // Technology
    'device-mobile': DeviceMobile,
    desktop: Desktop,
    laptop: Laptop,
    phone: Phone,
    camera: Camera,

    // Utilities & Services
    lightning: Lightning,
    wrench: Wrench,
    hammer: Hammer,
    'paint-brush': PaintBrush,

    // Pets & Animals
    dog: Dog,
    cat: Cat,
    'paw-print': PawPrint,

    // Family & Kids
    baby: Baby,
    'baby-carriage': BabyCarriage,

    // Nature & Environment
    tree: Tree,
    sun: Sun,
    moon: Moon,
    sparkle: Sparkle,

    // Communication & Media
    'envelope-simple': EnvelopeSimple,
    newspaper: Newspaper,
    article: Article,

    // Work & Business
    briefcase: Briefcase,

    // Travel & Location
    globe: Globe,
    'map-pin': MapPin,
};

// Predefined icon options for the icon picker
export const availableIcons: IconOption[] = [
    // Financial (most common for finance app)
    { name: 'wallet', component: Wallet, label: 'Wallet' },
    { name: 'credit-card', component: CreditCard, label: 'Credit Card' },
    { name: 'bank', component: Bank, label: 'Bank' },
    { name: 'coins', component: Coins, label: 'Coins' },
    { name: 'currency-dollar', component: CurrencyDollar, label: 'Dollar' },
    { name: 'trend-up', component: TrendUp, label: 'Trend Up' },
    { name: 'chart-line', component: ChartLine, label: 'Chart' },

    // Shopping
    { name: 'shopping-cart', component: ShoppingCart, label: 'Shopping Cart' },
    { name: 'storefront', component: Storefront, label: 'Store' },
    { name: 'gift', component: Gift, label: 'Gift' },

    // Home
    { name: 'house', component: House, label: 'House' },
    { name: 'buildings', component: Buildings, label: 'Buildings' },
    { name: 'building-office', component: BuildingOffice, label: 'Office' },

    // Transportation
    { name: 'car', component: Car, label: 'Car' },
    { name: 'bus', component: Bus, label: 'Bus' },
    { name: 'taxi', component: Taxi, label: 'Taxi' },
    { name: 'train', component: Train, label: 'Train' },
    { name: 'bicycle', component: Bicycle, label: 'Bicycle' },
    { name: 'airplane-tilt', component: AirplaneTilt, label: 'Airplane' },
    { name: 'boat', component: Boat, label: 'Boat' },

    // Food & Dining
    { name: 'utensils', component: ForkKnife, label: 'Dining' },
    { name: 'pizza', component: Pizza, label: 'Pizza' },
    { name: 'hamburger', component: Hamburger, label: 'Fast Food' },
    { name: 'fork-knife', component: ForkKnife, label: 'Restaurant' },
    { name: 'coffee', component: Coffee, label: 'Coffee' },
    { name: 'wine', component: Wine, label: 'Wine' },
    { name: 'beer-bottle', component: BeerBottle, label: 'Beer' },
    { name: 'ice-cream', component: IceCream, label: 'Dessert' },

    // Health & Fitness
    { name: 'first-aid', component: FirstAid, label: 'Medical' },
    { name: 'heartbeat', component: Heartbeat, label: 'Health' },
    { name: 'hospital', component: Hospital, label: 'Hospital' },
    { name: 'pill', component: Pill, label: 'Medicine' },
    { name: 'heart', component: Heart, label: 'Heart' },
    { name: 'barbell', component: Barbell, label: 'Fitness' },

    // Education
    { name: 'graduation-cap', component: GraduationCap, label: 'Education' },
    { name: 'book-open', component: BookOpen, label: 'Book' },
    { name: 'books', component: Books, label: 'Books' },
    { name: 'backpack', component: Backpack, label: 'School' },

    // Entertainment
    { name: 'game-controller', component: GameController, label: 'Gaming' },
    { name: 'film-slate', component: FilmSlate, label: 'Movies' },
    { name: 'television', component: Television, label: 'TV' },
    { name: 'music-note', component: MusicNote, label: 'Music' },
    { name: 'headphones', component: Headphones, label: 'Audio' },

    // Fashion
    { name: 't-shirt', component: TShirt, label: 'Clothing' },
    { name: 'scissors', component: Scissors, label: 'Salon' },
    { name: 'palette', component: Palette, label: 'Beauty' },

    // Technology
    { name: 'device-mobile', component: DeviceMobile, label: 'Mobile' },
    { name: 'desktop', component: Desktop, label: 'Computer' },
    { name: 'laptop', component: Laptop, label: 'Laptop' },
    { name: 'phone', component: Phone, label: 'Phone' },
    { name: 'camera', component: Camera, label: 'Camera' },

    // Utilities
    { name: 'lightning', component: Lightning, label: 'Electricity' },
    { name: 'wrench', component: Wrench, label: 'Maintenance' },
    { name: 'hammer', component: Hammer, label: 'Tools' },

    // Pets
    { name: 'dog', component: Dog, label: 'Dog' },
    { name: 'cat', component: Cat, label: 'Cat' },
    { name: 'paw-print', component: PawPrint, label: 'Pet' },

    // Family
    { name: 'baby', component: Baby, label: 'Baby' },
    { name: 'baby-carriage', component: BabyCarriage, label: 'Childcare' },

    // Nature
    { name: 'tree', component: Tree, label: 'Nature' },
    { name: 'plant', component: Plant, label: 'Plant' },
    { name: 'flower', component: Flower, label: 'Flower' },

    // Communication
    { name: 'envelope-simple', component: EnvelopeSimple, label: 'Mail' },
    { name: 'newspaper', component: Newspaper, label: 'News' },

    // Work
    { name: 'briefcase', component: Briefcase, label: 'Work' },

    // Travel
    { name: 'globe', component: Globe, label: 'Travel' },
    { name: 'map-pin', component: MapPin, label: 'Location' },
];

// Helper function to get icon component by name
export const getCategoryIcon = (iconName: string): React.ElementType => {
    return categoryIconMap[iconName] || Wallet;
};


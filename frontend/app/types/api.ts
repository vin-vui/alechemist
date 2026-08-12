export type CurrentStep = 'preparation' | 'mash' | 'boil' | 'yeast' | 'ferment' | 'completed'

export type StepType =
  | 'Preparation' | 'Mash' | 'Rinse' | 'Boil' | 'Yeast'
  | 'Primary' | 'Secondary' | 'Tertiary' | 'Bottle' | 'Dry Hop' | 'Aroma'

export interface ApiItem<T> { data: T }

export interface User {
  id: number
  name: string
  email: string
  two_factor_enabled: boolean
}

export interface Step {
  id: number
  quantity: number
  unit: 'g' | 'l' | '°C'
  field: string
  time: number | null
  type: StepType
}

export interface BrewingStep extends Step {
  date: string | null
  status: boolean
}

export interface Recipe {
  id: number
  name: string
  type: string | null
  method: string | null
  ferment: string | null
  volume: number
  efficiency: string | null
  color: string | null
  bitterness: string | null
  alcohol: string | null
  boil_time: number
  initial_density: number
  final_density: number
  before_boil_density: number | null
  carbonation: number
  created_at: string
  updated_at: string
  brewings_count?: number
  steps?: Step[]
}

export interface Brewing {
  id: number
  name: string
  volume: number
  alcohol: string | null
  boil_time: number
  boil_start: string | null
  ferment_start: string | null
  ferment_end: string | null
  initial_density: number
  final_density: number
  before_boil_density: number | null
  carbonation: number
  date_start: string
  current_step: CurrentStep | null
  note: string | null
  recipe_id: number
  created_at: string
  updated_at: string
  steps?: BrewingStep[]
  recipe?: Recipe
}

export interface Dashboard {
  recipe_count: number
  ferment_brewings: Brewing[]
  in_progress_brewings: Brewing[]
}
